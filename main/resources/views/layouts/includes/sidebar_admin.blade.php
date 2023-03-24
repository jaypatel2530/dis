    
    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#managesuperdistributors" aria-expanded="true" aria-controls="managesuperdistributors">-->
    <!--      <i class="fas fa-fw fa-user-plus"></i>-->
    <!--      <span>Super Distributors</span>-->
    <!--    </a>-->
    <!--    <div id="managesuperdistributors" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage Super Distributor</h6>-->
    <!--        <a class="collapse-item" href="{{ route('get:add_super_distributor') }}">Add Super Distributor</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:manage_super_distributors') }}">Manage Super Distributors</a>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->
    
    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#managedistributors" aria-expanded="true" aria-controls="managedistributors">-->
    <!--      <i class="fas fa-fw fa-user-plus"></i>-->
    <!--      <span>Distributors</span>-->
    <!--    </a>-->
    <!--    <div id="managedistributors" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage Distributor</h6>-->
    <!--        <a class="collapse-item" href="{{ route('get:add_distributor') }}">Add Distributor</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:manage_distributors') }}">Manage Distributors</a>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#manageretailer" aria-expanded="true" aria-controls="manageretailer">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Members</span>
        </a>
        <div id="manageretailer" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Members</h6>
            <!--<a class="collapse-item" href="{{ route('get:add_retailer') }}">Add Retailer</a>-->
            <a class="collapse-item" href="{{ route('get:manage_retailer') }}">Manage Members</a>
            <a class="collapse-item" href="{{ route('get:scanner') }}">QR Scanner</a>
            <!--<a class="collapse-item" href="{{ route('get:retailers_manage_services') }}">Manage Services</a>-->
          </div>
        </div>
    </li>
    
    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bank_details" aria-expanded="true" aria-controls="bank_details">-->
    <!--      <i class="fas fa-fw fa-landmark"></i>-->
    <!--      <span>Banks</span>-->
    <!--    </a>-->
    <!--    <div id="bank_details" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage Banks</h6>-->
    <!--        <a class="collapse-item" href="{{ route('get:add_bank_detail') }}">Add Bank</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:manage_bank_details') }}">Banks Details</a>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->
    
    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#manage_kyc" aria-expanded="true" aria-controls="manage_kyc">-->
    <!--      <i class="fas fa-fw fa-landmark"></i>-->
    <!--      <span>Manage KYC</span>-->
    <!--    </a>-->
    <!--    <div id="manage_kyc" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage KYC</h6>-->
    <!--            <a class="collapse-item" href="{{ route('get:retailers_pending_kyc') }}">Retailers KYC</a>-->
    <!--            <a class="collapse-item" href="{{ route('get:distributors_pending_kyc') }}">Distributors KYC</a>-->
    <!--            <a class="collapse-item" href="{{ route('get:super_distributors_pending_kyc') }}">Super Distributors KYC</a>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->

    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#mappings" aria-expanded="true" aria-controls="mappings">-->
    <!--      <i class="fas fa-fw fa-user-plus"></i>-->
    <!--      <span>Mapping</span>-->
    <!--    </a>-->
    <!--    <div id="mappings" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage Retailers</h6>-->
    <!--        <a class="collapse-item" href="{{ route('get:distributor_mapping') }}">Distributor Mapping</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:retailer_mapping') }}">Retailer Mapping</a>-->
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->
    
    <!--<li class="nav-item">-->
    <!--    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#moneyreqeusts" aria-expanded="true" aria-controls="moneyreqeusts">-->
    <!--      <i class="fas fa-fw fa-rupee-sign"></i>-->
    <!--      <span>Money Requests</span>-->
    <!--    </a>-->
    <!--    <div id="moneyreqeusts" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">-->
    <!--      <div class="bg-white py-2 collapse-inner rounded">-->
    <!--        <h6 class="collapse-header">Manage Money Requests</h6>-->
            
    <!--        <a class="collapse-item" href="{{ route('get:super_distributor_money_requests') }}">Super Distributors Money Requests</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:super_distributor_money_requests_report') }}">Super Distributors Money Requests Report</a>-->
            
    <!--        <a class="collapse-item" href="{{ route('get:distributor_money_requests') }}">Distributors Money Requests</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:distributor_money_requests_report') }}">Distributors Money Requests Report</a>-->
            
    <!--        <a class="collapse-item" href="{{ route('get:retailer_money_requests') }}">Retailers Money Requests</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:retailers_money_requests_report') }}">Retailers Money Requests Report</a>-->
    <!--        <a class="collapse-item" href="{{ route('get:mapped_retailers_money_requests_report') }}">Mapped Retailers Money Requests Report</a>-->
            
    <!--      </div>-->
    <!--    </div>-->
    <!--</li>-->
    
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#notifications" aria-expanded="true" aria-controls="notifications">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Notifications</span>
        </a>
        <div id="notifications" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Notifications</h6>
            <a class="collapse-item" href="{{ route('get:send_notification') }}">Send Notification</a>
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#settings" aria-expanded="true" aria-controls="settings">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Settings</span>
        </a>
        <div id="settings" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Settings</h6>
            <a class="collapse-item" href="{{ route('get:app_settings') }}">Terms And Conditions</a>
            <!--<a class="collapse-item" href="{{ route('get:manage_app_banners') }}">Manage App Banners</a>-->
            <!--<a class="collapse-item" href="{{ route('get:add_google_code') }}">Add Google Code</a>-->
            <!--<a class="collapse-item" href="{{ route('get:manage_google_code') }}">Manage Google Code</a>-->
            <!--<a class="collapse-item" href="{{ route('get:purchase_google_code') }}">Purchase Google Code</a>-->
            <!--<a class="collapse-item" href="{{ route('get:logs') }}">Logs</a>-->
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#supports" aria-expanded="true" aria-controls="supports">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Events</span>
        </a>
        <div id="supports" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Events</h6>
            <a class="collapse-item" href="{{ route('get:add_event') }}">Add Event</a>
            <a class="collapse-item" href="{{ route('get:manage_event') }}">Manage Event</a>
            <a class="collapse-item" href="{{ route('get:event_attendance') }}">Event Attendance</a>
          </div>
        </div>
    </li>