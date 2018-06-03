<?php

namespace Vanguard\Http\Controllers\Web;

use Vanguard\Events\User\ChangedAvatar;
use Vanguard\Events\User\TwoFactorDisabled;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Events\User\UpdatedProfileDetails;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\User\EnableTwoFactorRequest;
use Vanguard\Http\Requests\User\UpdateProfileDetailsRequest;
use Vanguard\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use Vanguard\Repositories\Activity\ActivityRepository;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\Session\SessionRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Services\Upload\UserAvatarManager;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use Auth;
use Authy;
use Illuminate\Http\Request;

/**
 * Class ProfileController
 * @package Vanguard\Http\Controllers
 */
class ProfileController extends Controller
{
    /**
     * @var User
     */
    protected $theUser;
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * UsersController constructor.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('auth');
        $this->middleware('session.database', ['only' => ['sessions', 'invalidateSession']]);

        $this->users = $users;

        $this->middleware(function ($request, $next) {
            $this->theUser = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display user's profile page.
     *
     * @param RoleRepository $rolesRepo
     * @param CountryRepository $countryRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(RoleRepository $rolesRepo, CountryRepository $countryRepository)
    {
        $user = $this->theUser;
        $edit = true;
        $roles = $rolesRepo->lists();
        $countries = [0 => 'Select a Country'] + $countryRepository->lists()->toArray();
        $socialLogins = $this->users->getUserSocialLogins($this->theUser->id);
        $statuses = UserStatus::lists();

        return view(
            'user/profile',
            compact('user', 'edit', 'roles', 'countries', 'socialLogins', 'statuses')
        );
    }

    /**
     * Update profile details.
     *
     * @param UpdateProfileDetailsRequest $request
     * @return mixed
     */
    public function updateDetails(UpdateProfileDetailsRequest $request)
    {
        $this->users->update($this->theUser->id, $request->except('role_id', 'status'));

        event(new UpdatedProfileDetails);

        return redirect()->back()
            ->withSuccess(trans('app.profile_updated_successfully'));
    }

    /**
     * Upload and update user's avatar.
     *
     * @param Request $request
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatar(Request $request, UserAvatarManager $avatarManager)
    {
        $this->validate($request, [
            'avatar' => 'image'
        ]);

        $name = $avatarManager->uploadAndCropAvatar(
            $this->theUser,
            $request->file('avatar'),
            $request->get('points')
        );

        if ($name) {
            return $this->handleAvatarUpdate($name);
        }

        return redirect()->route('profile')
            ->withErrors(trans('app.avatar_not_changed'));
    }

    /**
     * Update avatar for currently logged in user
     * and fire appropriate event.
     *
     * @param $avatar
     * @return mixed
     */
    private function handleAvatarUpdate($avatar)
    {
        $this->users->update($this->theUser->id, ['avatar' => $avatar]);

        event(new ChangedAvatar);

        return redirect()->route('profile')
            ->withSuccess(trans('app.avatar_changed'));
    }

    /**
     * Update user's avatar from external location/url.
     *
     * @param Request $request
     * @param UserAvatarManager $avatarManager
     * @return mixed
     */
    public function updateAvatarExternal(Request $request, UserAvatarManager $avatarManager)
    {
        $avatarManager->deleteAvatarIfUploaded($this->theUser);

        return $this->handleAvatarUpdate($request->get('url'));
    }

    /**
     * Update user's login details.
     *
     * @param UpdateProfileLoginDetailsRequest $request
     * @return mixed
     */
    public function updateLoginDetails(UpdateProfileLoginDetailsRequest $request)
    {
        $data = $request->except('role', 'status');

        // If password is not provided, then we will
        // just remove it from $data array and do not change it
        if (trim($data['password']) == '') {
            unset($data['password']);

            unset($data['password_confirmation']);
        }

        $this->users->update($this->theUser->id, $data);

        return redirect()->route('profile')
            ->withSuccess(trans('app.login_updated'));
    }

    /**
     * Enable 2FA for currently logged user.
     *
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableTwoFactorAuth(EnableTwoFactorRequest $request)
    {
        if (Authy::isEnabled($this->theUser)) {
            return redirect()->route('user.edit', $this->theUser->id)
                ->withErrors(trans('app.2fa_already_enabled'));
        }

        $this->theUser->setAuthPhoneInformation($request->country_code, $request->phone_number);

        Authy::register($this->theUser);

        $this->theUser->save();

        event(new TwoFactorEnabled);

        return redirect()->route('profile')
            ->withSuccess(trans('app.2fa_enabled'));
    }

    /**
     * Disable 2FA for currently logged user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableTwoFactorAuth()
    {
        if (! Authy::isEnabled($this->theUser)) {
            return redirect()->route('profile')
                ->withErrors(trans('app.2fa_not_enabled_for_this_user'));
        }

        Authy::delete($this->theUser);

        $this->theUser->save();

        event(new TwoFactorDisabled);

        return redirect()->route('profile')
            ->withSuccess(trans('app.2fa_disabled'));
    }

    /**
     * Display user activity log.
     *
     * @param ActivityRepository $activitiesRepo
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activity(ActivityRepository $activitiesRepo, Request $request)
    {
        $user = $this->theUser;

        $activities = $activitiesRepo->paginateActivitiesForUser(
            $user->id,
            $perPage = 20,
            $request->get('search')
        );

        return view('activity.index', compact('activities', 'user'));
    }


    /**
     * Display active sessions for current user.
     *
     * @param SessionRepository $sessionRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sessions(SessionRepository $sessionRepository)
    {
        $profile = true;
        $user = $this->theUser;
        $sessions = $sessionRepository->getUserSessions($user->id);

        return view('user.sessions', compact('sessions', 'user', 'profile'));
    }

    /**
     * Invalidate user's session.
     *
     * @param $session \stdClass Session object.
     * @param SessionRepository $sessionRepository
     * @return mixed
     */
    public function invalidateSession($session, SessionRepository $sessionRepository)
    {
        $sessionRepository->invalidateSession($session->id);

        return redirect()->route('profile.sessions')
            ->withSuccess(trans('app.session_invalidated'));
    }

    public function tree()
    {
        $chart =  [
        "container" => "#basic-example",
        "connectors" => [
            "type" => "step",
            "style" => ["stroke" => "#ffffff"]
        ],
        "node" =>  [
             "HTMLclass" => "nodeExample1"
        ]
    ];
        if(request()->has("json")){
            //  dump(request()->data);
          $chart = ['container' => "#tree", "connectors" => ['type' => 'step'], "node" => ["HTMLclass" => "nodeExample1"]];
            $id = Auth::user()->id;
            $data = \DB::select(
                                "select  id,
                                username,
                                first_name,
                                last_name,
                                created_at,
                                sponsor_id,
                                email,
                                referral 
                                from    (select * from users
                                order by referral, id) products_sorted,
                                (select @pv := '$id') initialisation
                                where   find_in_set(referral, @pv)
                                and     length(@pv := concat(@pv, ',', id))
                                order by id desc
                                "


                            );
                            
            $users[] = $chart;  

              $users = [];
            // dump($data);
            foreach ($data as $u ) {
              $user = [];
            $user['innerHTML'] = $this->getHtml($u);
            $user['connectors'] = [
            "type" => "step",
            "style" => ["stroke" => "#ccc"]
            ];
            // $user["image"] = "/assets/img/profile.png"; 
            // $user["stackChildren"] = true; 
            
            if(isset($users[$u->id])){
               $user['children'] = $users[$u->id]['children'];
               unset($users[$u->id]);
            }
                $users[$u->referral]['children'][] =  $user;
            }
            // dump($users);
            $u = \Auth::user();
            $user = [];
            $user['innerHTML'] = $this->getHtml($u);
                        $user['connectors'] = [
            "type" => "step",
            "style" => ["stroke" => "#ccc"]
            ];
            if(isset($users[$u->id]['children']))
            {
                $user['children'] = $users[$u->id]['children'];
            }
           
            $data = [];
            // dd($user);
            $data['nodeStructure'] = $user;
            $data['chart'] = $chart;
            return $data;   
            return request()->data;
            // dd(request()->data);

            $data = [];
            $chart = ['container' => "#basic-example", "connectors" => ['type' => 'step'], "node" => ["HTMLclass" => "nodeExample1"]];
            
            $data[] = $chart; 
            dd($users);
            return ($users);

    //             ceo = {
    //     text: {
    //         name: "Mark Hill",
    //         title: "Chief executive officer",
    //         contact: "Tel: 01 213 123 134",
    //     },
    //     image: "../headshots/2.jpg"
    // },
            return $t;
        }
        return view('dashboard.tree');
    }

    public function getHtml($user){
        if(!isset($user->first_name)){
            // dd($user);
        }
        $html = "";
        $html .= "<div>";
        $html .= "<span style='font-weight:bold'>". $user->first_name . " ". $user->last_name ."</span>";
        $html .= "<span style='font-weight:bold'>". $user->first_name . " ". $user->last_name ."</span>";
        $html .= "<br>";
        $html .= $user->email;  
        $html .= "<br>";
        $html .= "Joined: ".\Carbon\Carbon::parse($user->created_at)->format('M d Y');
        $html .= "<br>";
        $html .= "Sponsor: ".$user->sponsor_id;
        $html .= "</div>";
        return $html;
    }
}
