@if(Auth::User()->user_type == 1)
@include('pages.dashboard_admin');
@else
@include('pages.dashboard_member');
@endif