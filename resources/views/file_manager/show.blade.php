@extends('template')


@section('title')
   File Manager
@endsection



@section('content')
 
    <ol class="breadcrumb mb-4">
        <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="siteip"></span></b></li>
        <li class="ml-1 breadcrumb-item active">ALIASES:<b><span class="ml-1" id="sitealiases"></span></b></li>
        <li class="ml-1 breadcrumb-item active">PHP:<b><span class="ml-1" id="sitephp"></span></b></li>
        <li class="ml-1 breadcrumb-item active">DIR:<b><span class="ml-1">/home/</span><span
                    id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></b></li>
    </ol>
    <div class="row">
        <div class="col">
            <div class="card mb-4">

                <table class="table table-hover table-border">
                    <thead class="table-primary">
                        <tr>
                            <th>Files</th>
                            <th>Sizes</th>
                            <th>Modification date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pathContents as $content)
                            @if ($content['type'] === 'file')
                                <tr>
                                    
                                    <td><?xml version="1.0" ?><svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><path d="m5 1030.4c-1.1046 0-2 0.9-2 2v8 4 6c0 1.1 0.8954 2 2 2h14c1.105 0 2-0.9 2-2v-6-4-4l-6-6h-10z" fill="#95a5a6"/><path d="m5 1029.4c-1.1046 0-2 0.9-2 2v8 4 6c0 1.1 0.8954 2 2 2h14c1.105 0 2-0.9 2-2v-6-4-4l-6-6h-10z" fill="#bdc3c7"/><path d="m21 1035.4-6-6v4c0 1.1 0.895 2 2 2h4z" fill="#95a5a6"/><path d="m6 8v1h12v-1h-12zm0 3v1h12v-1h-12zm0 3v1h12v-1h-12zm0 3v1h12v-1h-12z" fill="#95a5a6" transform="translate(0 1028.4)"/></g></svg> {{ $content['filename'] }}</td>
                                    <td>{{ number_format($content['size'] /1000) }} KB</td>
                                    <td>{{$content['last_modified']}}</td>
                                </tr>
                            @else
                                <tr>
                                    <td> <?xml version="1.0" ?><svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><path d="m2 1033.4c-1.1046 0-2 0.9-2 2v14c0 1.1 0.89543 2 2 2h20c1.105 0 2-0.9 2-2v-14c0-1.1-0.895-2-2-2h-20z" fill="#2980b9"/><path d="m3 1029.4c-1.1046 0-2 0.9-2 2v14c0 1.1 0.8954 2 2 2h11 5 2c1.105 0 2-0.9 2-2v-9-3c0-1.1-0.895-2-2-2h-2-5-1l-3-2h-7z" fill="#2980b9"/><path d="m23 1042.4v-8c0-1.1-0.895-2-2-2h-11-5-2c-1.1046 0-2 0.9-2 2v8h22z" fill="#bdc3c7"/><path d="m2 1033.4c-1.1046 0-2 0.9-2 2v6 1 6c0 1.1 0.89543 2 2 2h20c1.105 0 2-0.9 2-2v-6-1-6c0-1.1-0.895-2-2-2h-20z" fill="#3498db"/></g></svg> <a href="{{route('files.show', $content['folder_name'])}}">{{ $content['folder_name'] }}</a></td>
                                    <td> - </td>
                                    <td> - </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>



    </div>
@endsection
