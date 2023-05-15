use App\Models\Mysqluser;
@extends('template')


@section('title')
    Database
@endsection



@section('content')

    <body>
        @if (Session::has('success'))
            <div class="alert alert-success">
                <p>{{ Session::get('success') }}</p>
            </div>
        @elseif (Session::has('failed'))
            <div class="alert alert-danger">
                <p>{{ Session::get('failed') }}</p>
            </div>
        @endif
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            Create new Database
                        </div>
                        <div class="card-body">
                            <form action="{{ route('createdatab') }}" method="post">
                                @csrf
                                <input type="text" name="data_name" class="form-control mb-4"
                                    placeholder="database name">
                                <button class="btn btn-primary">Create database</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            MYSQL Users
                        </div>
                        <div class="card-body">
                            <p>Add New Users</p>
                            <div class="text-center">

                                <form action="{{ route('createuser') }}" method="post">
                                    @csrf
                                    <input type="text" name="username" class="form-control mb-4" placeholder="username">
                                    <input type="password" name="password" class="form-control mb-4" placeholder="password">
                                    <input type="password" name="conf_password" class="form-control mb-4"
                                        placeholder="Confirm password">
                                    <button class="btn btn-primary">Create User</button>
                                </form>
                                <div class="space"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            Add User to Database
                        </div>
                        <div class="card-body">
                            {{-- <p>Add New Users</p> --}}
                            <div>

                                <form action="{{ route('linkdatabuser') }}" method="post">
                                    @csrf
                                    <label for="" class="">User</label>
                                    <select name="username" class="form-control mb-4" id="username">
                                        @foreach ($mysqluser as $user)
                                            <option value="{{ $user->id }}">{{ $user->username }}</option>
                                        @endforeach
                                    </select>

                                    <label for="">Database</label>
                                    <select name="database" class="form-control mb-4" id="database_name">
                                        @foreach ($userdata as $database)
                                            <option value="{{ $database->id }}">{{ $database->database_name }}</option>
                                        @endforeach
                                    </select>

                                    <button class="btn btn-primary">Add</button>
                                </form>
                                <div class="space"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            List of Databases
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Database Name</th>
                                        <th scope="col">Mysql Username</th>
                                        {{-- <th scope="col">Handle</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userdata as $databases)
                                        <tr>
                                            <td>{{ $databases->database_name }}</td>
                                            <td>{{ $databases->mysqluser->username }}</td>
                                            {{-- <td>@mdo</td> --}}
                                        </tr>
                                        {{-- <option value="{{ $user->id }}">{{ $user->username }}</option> --}}
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            List of MYSQL Users
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        {{-- <th scope="col">#</th> --}}
                                        <th scope="col">Username</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mysqluser as $users)
                                        <tr>
                                            <td>{{ $users->username }}</td>
                                        </tr>
                                        {{-- <option value="{{ $user->id }}">{{ $user->username }}</option> --}}
                                    @endforeach

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
@endsection
