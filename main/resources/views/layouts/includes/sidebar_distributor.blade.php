    
    <li class="nav-item">
        <a class="nav-link" href="{{ route('get:my_passbook') }}">
          <i class="fa fa-fw fa-address-card"></i>
          <span>My Passbook</span></a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#upi_request" aria-expanded="true" aria-controls="upi_request">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>UPI Request</span>
        </a>
        <div id="upi_request" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage UPI Request</h6>
            <a class="collapse-item" href="{{ route('get:upi_request') }}">UPI Request</a>
            <a class="collapse-item" href="{{ route('get:upi_request_report') }}">UPI Request Report</a>
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#manageretailer" aria-expanded="true" aria-controls="manageretailer">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Retailers</span>
        </a>
        <div id="manageretailer" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Retailers</h6>
            <a class="collapse-item" href="{{ route('get:add_retailer') }}">Add Retailer</a>
            <a class="collapse-item" href="{{ route('get:manage_retailer') }}">Manage Retailers</a>
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#manageretailerids" aria-expanded="true" aria-controls="manageretailerids">
          <i class="fas fa-fw fa-user-plus"></i>
          <span>Retailers Ids</span>
        </a>
        <div id="manageretailerids" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Retailers Ids</h6>
            <a class="collapse-item" href="{{ route('get:purchase_retailer_ids') }}">Purchase Retailer IDs</a>
            <a class="collapse-item" href="{{ route('get:purchase_retailer_ids_report') }}">Retailer IDs Report</a>
            <!--<a class="collapse-item" href="{{ route('get:manage_retailer') }}">Manage Retailers</a>-->
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bank_details" aria-expanded="true" aria-controls="bank_details">
          <i class="fas fa-fw fa-landmark"></i>
          <span>Banks</span>
        </a>
        <div id="bank_details" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Banks</h6>
            <a class="collapse-item" href="{{ route('get:add_bank_detail') }}">Add Bank</a>
            <a class="collapse-item" href="{{ route('get:manage_bank_details') }}">Banks Details</a>
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#moneyreqeusts" aria-expanded="true" aria-controls="moneyreqeusts">
          <i class="fas fa-fw fa-rupee-sign"></i>
          <span>Money Requests</span>
        </a>
        <div id="moneyreqeusts" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Money Requests</h6>
            <a class="collapse-item" href="{{ route('get:add_money') }}">Add Money</a>
            <a class="collapse-item" href="{{ route('get:add_money_report') }}">Add Money Report</a>
            <a class="collapse-item" href="{{ route('get:money_requests') }}">Money Requests</a>
            <a class="collapse-item" href="{{ route('get:money_requests_report') }}">Money Requests Report</a>
          </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#reports" aria-expanded="true" aria-controls="reports">
          <i class="fas fa-fw fa-file"></i>
          <span>Reports</span>
        </a>
        <div id="reports" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Reports</h6>
            <a class="collapse-item" href="{{ route('get:retailers_transactions_report') }}">Retailers Transactions</a>
            <a class="collapse-item" href="{{ route('get:fund_added_report') }}">Fund Added Report</a>
            <a class="collapse-item" href="{{ route('get:commission_report') }}">Commission Report</a>
          </div>
        </div>
    </li>
    
    