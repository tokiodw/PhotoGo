<nav class="navbar navbar-expand-lg navbar-dark bg-info bg-gradient">
    <div class="container-fluid">
        <span>
            <i class="fa-solid fa-camera" style="color: white"></i>
            <a class="navbar-brand" href="#">
                PhotoGo
            </a>
        </span>
        <div>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <a class="nav-link active" aria-current="page" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
            this.closest('form').submit();">ログアウト</a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
