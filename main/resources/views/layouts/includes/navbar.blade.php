
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            @if(Auth::user()->user_type == 1)
          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          @endif  
          <div class="navbarlogo">
              <div class="sidebar-brand-icon">
                <a class="align-items-center justify-content-center" href="{{ route('dashboard') }}">
                    <img class="img-thumbnail" src="{{ asset('theme/img/logoDIS.jpg') }}">
                </a>
              </div>
          </div>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
              </li>

          <!-- <div class="topbar-divider d-none d-sm-block"></div> -->
            @if(Auth::user()->user_type == 3 || Auth::user()->user_type == 4)
            <li class="nav-item dropdown no-arrow mx-1" style="margin-top: 20px;color: #fff;">
              
                @if( App\Model\KycDoc::getKycStatus(Auth::user()->id)  == 3)
                    <a class="btn btn-sm btn-primary" href="{{ route('get:upload_kyc_documents') }}"><i class="fa fa-upload" aria-hidden="true"></i> Upload KYC</a>
                @elseif( App\Model\KycDoc::getKycStatus(Auth::user()->id) == 0)
                    <a class="btn btn-sm btn-warning" ><i class="fa fa-clock" aria-hidden="true"></i> KYC Pending</a>
                @elseif( App\Model\KycDoc::getKycStatus(Auth::user()->id) == 1)
                    <a class="btn btn-sm btn-success"><i class="fa fa-check" aria-hidden="true"></i> KYC Approved</a>
                @elseif( App\Model\KycDoc::getKycStatus(Auth::user()->id) == 2)
                    <a class="btn btn-sm btn-danger" href="{{ route('get:upload_kyc_documents') }}"><i class="fas fa-times"></i> KYC Rejected</a>
                @endif
            
            </li>
            @endif  
            
            
            @if(Auth::user()->user_type == 1)
            <li class="nav-item dropdown no-arrow mx-1" style="margin-top: 20px;color: #fff;">
                
                </li>
                
                <!-- <li class="nav-item dropdown no-arrow mx-1">-->
                <!--  <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" -->
                <!--  aria-haspopup="true" aria-expanded="false">-->
                   
                <!--    <span class="text-white1" title="KYC"> -->
                <!--        <strong>-->
                <!--            Super Distributor - <span class="badge badge-primary">{{ $super_distributor_pending_kyc }}</span> | -->
                <!--            Distributor - <span class="badge badge-primary">{{ $distributor_pending_kyc }}</span> |-->
                <!--            Retailer - <span class="badge badge-primary">{{ $retailer_pending_kyc }}</span>-->
                <!--        </strong>-->
                <!--    </span>-->
                <!--  </a>-->
                <!--</li>-->
            
            @endif
            
            
            <!--<li class="nav-item dropdown no-arrow mx-1">-->
            <!--  <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" -->
            <!--  aria-haspopup="true" aria-expanded="false">-->
            <!--    <i class="fas fa-wallet fa-fw text-white1"></i>-->
            <!--    <span class="text-white1" title="Wallet">:  <small><i class="fas fa-fw fa-rupee-sign"></i></small><strong>{{ number_format(Auth::user()->wallet,2) }}</strong></span>-->
            <!--  </a>-->
            <!--</li>-->

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                
                <img class="img-profile rounded-circle" alt="img" src="{{ asset('uploads/profile_pics/'.Auth::user()->profile_pic) }}">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('dashboard') }}">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Dashboard
                </a>
                <a class="dropdown-item" href="{{ route('get:profile') }}">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile
                </a>
                <!--<a class="dropdown-item" href="{{ route('get:app_settings') }}">-->
                <!--  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings-->
                <!--</a>-->
                <!-- <a class="dropdown-item" href="#">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Activity Log
                </a> -->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        