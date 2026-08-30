<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">System Warranty</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="dist/img/user200.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"> @if(isset(Auth::User()->username))
                    <strong>{{ strtoupper (Auth::User()->name) }}</strong>
                    @endif</a>
            </div>


        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

                <li class="nav-item">
                    <a href="/" class="nav-link {{ Request::url() == url('/') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/customer" class="nav-link {{ Request::url() == url('/customer') ? 'active' : '' }}">
                        <i class="nav-icon fas fas fa-users"></i>
                        <p>
                            Data Customer
                        </p>
                    </a>
                </li>
                <li class="nav-header">TRANSACTION</li>
                <li class="nav-item"><a href="/order" class="nav-link {{ Request::is('order*') ? 'active' : '' }}"><i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Order</p>
                    </a></li>
                <li class="nav-item"><a href="/invoice" class="nav-link {{ Request::is('invoice*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-invoice"></i>
                        <p>Invoice</p>
                    </a></li>
                <li class="nav-item">
                    <a href="/warranty" class="nav-link {{ Request::url() == url('/warranty') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shield-alt"></i>
                        <p>
                            Warranty
                        </p>
                    </a>
                </li>
                <li class="nav-header">MASTER DATA</li>
                <li class="nav-item"><a href="/vehicle" class="nav-link {{ Request::url() == url('/vehicle') ? 'active' : '' }}"><i class="nav-icon fas fa-car"></i>
                        <p>Vehicle</p>
                    </a></li>
                <li class="nav-item"><a href="/building" class="nav-link {{ Request::url() == url('/building') ? 'active' : '' }}"><i class="nav-icon fas fa-building"></i>
                        <p>Building</p>
                    </a></li>

                <li class="nav-item">
                    <a href="/product" class="nav-link {{ Request::url() == url('/product') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>
                            Data Product
                        </p>
                    </a>
                </li>
                <li class="nav-item"><a href="/product-type" class="nav-link {{ Request::url() == url('/product-type') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i>
                        <p>Product Type</p>
                    </a></li>
                <li class="nav-item"><a href="/product-variant" class="nav-link {{ Request::url() == url('/product-variant') ? 'active' : '' }}"><i class="nav-icon fas fa-list-alt"></i>
                        <p>Product Variant</p>
                    </a></li>
                <li class="nav-item"><a href="/treatment" class="nav-link {{ Request::url() == url('/treatment') ? 'active' : '' }}"><i class="nav-icon fas fa-tools"></i>
                        <p>Treatment</p>
                    </a></li>
                <li class="nav-item"><a href="/technician" class="nav-link {{ Request::url() == url('/technician') ? 'active' : '' }}"><i class="nav-icon fas fa-user-cog"></i>
                        <p>Teknisi</p>
                    </a></li>
                <li class="nav-item">
                    <a href="/user" class="nav-link {{ Request::url() == url('/user') ? 'active' : '' }}">
                        <i class="nav-icon fas fas fa-user"></i>
                        <p>
                            Data User
                        </p>
                    </a>
                </li>

                <li class="nav-header">REPORTS</li>
                <li class="nav-item">
                    <a href="/laporan-mobil" class="nav-link {{ Request::url() == url('/laporan-mobil') ? 'active' : '' }}">
                        <i class="nav-icon fas fas fa-file"></i>
                        <p>
                            Report Mobil
                        </p>
                    </a>
                </li>
                <li class="nav-item"><a href="/laporan-building" class="nav-link {{ Request::url() == url('/laporan-building') ? 'active' : '' }}"><i class="nav-icon fas fa-building"></i><p>Laporan Building</p></a></li>
                <li class="nav-item"><a href="/laporan-invoice" class="nav-link {{ Request::url() == url('/laporan-invoice') ? 'active' : '' }}"><i class="nav-icon fas fa-file-invoice"></i><p>Laporan Invoice</p></a></li>








                <li class="nav-item">
                    <a href="#" class="nav-link" style="cursor: pointer;" onclick="Logout();">
                        <i class=" nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Logout
                        </p>
                    </a>
                </li>


            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
