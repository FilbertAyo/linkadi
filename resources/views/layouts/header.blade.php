  <nav class="custom-navbar navbar navbar navbar-expand-md navbar-dark bg-dark" arial-label="Furni navigation bar">

      <div class="container">
          <a class="navbar-brand" href="index.html"><img src="images/linkadiwhite.png" alt=""
                  style="height: 40px;"></a>

          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni"
              aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarsFurni">
              <ul class="custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0">
                  <li class="nav-item active">
                      <a class="nav-link" href="{{ route('welcome') }}">Home</a>
                  </li>
                  <li><a class="nav-link" href="{{ route('shop') }}">Shop</a></li>
                  <li><a class="nav-link" href="about.html">About us</a></li>
                  <li><a class="nav-link" href="services.html">Services</a></li>
                  <li><a class="nav-link" href="blog.html">Blog</a></li>
                  <li><a class="nav-link" href="{{ route('contact') }}">Contact us</a></li>
              </ul>

              <ul class="custom-navbar-cta navbar-nav mb-2 mb-md-0 ms-5">
                  @auth
                      <li>
                          <a class="nav-link" href="{{ route('dashboard') }}">
                              <img src="{{ asset('images/user.svg') }}" alt="User" />
                          </a>
                      </li>
                  @endauth

                  @guest
                      <li>
                          <a class="nav-link btn btn-white text-white px-3" href="{{ route('login') }}">
                              Login
                          </a>
                      </li>
                  @endguest

                  <li><a class="nav-link" href="{{ route('cart.index') }}"><img src="images/cart.svg"></a></li>
              </ul>
          </div>
      </div>

  </nav>
