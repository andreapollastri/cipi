@extends('template')


@section('title')
    Data
@endsection



@section('content')

    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fab fa-github fs-fw mr-1"></i>
                            Create new Database
                        </div>
                        <div class="card-body">
                            <form action="{{ route('create') }}" method="post">
                                <input type="text" name="data_name" class="form-control mb-4" placeholder="database name">
                                <button class="btn btn-primary">Create database</button>
                            </form>
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
                                    <label for="" class="">User</label>
                                        <select name="username" class="form-control mb-4" id="username">
                                            @foreach ($mysqluser as $user)
                                            <option value="{{ $user->id }}">{{ $user->username }}</option>
                                            @endforeach
                                        </select>
                                  
                                    <label for="">Database</label>
                                    <select name="database" class="form-control mb-4" id="database_name">
                                        @foreach($userdata as $database)
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
        </div>
    </body>
@endsection
