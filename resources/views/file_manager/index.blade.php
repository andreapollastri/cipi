@extends('template')


@section('title')
    File Manager
@endsection

@section('css')
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            font-family: "Open Sans", sans-serif;
            padding: 0;
            margin: 0;
        }

        input[type=text]{
            border:2px solid #000;
        }

   

        #context-menu,
        #context-folder-menu {
            position: fixed;
            z-index: 10000;
            width: 200px;
            background: #1b1a1a;
            border-radius: 5px;
            transform: scale(0);
            transform-origin: top left;
        }

        #context-menu.visible,
        #context-folder-menu.visible {
            transform: scale(1);
            transition: transform 200ms ease-in-out;
        }

        #context-menu .item,
        #context-folder-menu .item {
            padding: 8px 10px;
            font-size: 15px;
            color: #eee;
            cursor: pointer;
            border-radius: inherit;
        }

        #context-menu .item:hover,
        #context-folder-menu .item:hover {
            background: #343434;
        }

        td a {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>
@endsection


@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">x</button>
            {{ Session::get('success') }}
        </div>
    @endif

    <ol class="breadcrumb mb-4">
        <li class="ml-1 breadcrumb-item active"><a
                href="{{ $app->make('url')->to('/files?'.Str::before($queryPath,'/')) }}">{{ $app->make('url')->to('/files') }}</a><b><span
                    class="ml-1" id="siteip"></span></b>
        </li>
        @if (!is_null($headers))
            @foreach ($headers as $header)
                @if (Str::afterLast($queryPath, '/') == $header)
                    <li class="ml-1 breadcrumb-item active"><a href="#">{{ $header }}</a><b><span class="ml-1"
                                id="siteip"></span></b>
                    </li>
                @else
                    <li class="ml-1 breadcrumb-item active"><a
                            href="{{ $app->make('url')->to('/files?'.Str::beforeLast($queryPath, '/')) }}">{{ $header }}</a><b><span
                                class="ml-1" id="siteip"></span></b>
                    </li>
                @endif
            @endforeach
        @endif

        {{--  --}}
    </ol>
    <div class="row">
        <div class="col">
            <div class="card mb-4">
                <div class="text-right m-2">

                    <?xml version="1.0" encoding="iso-8859-1"?>
                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                    <svg id="new-file" style="cursor:pointer" title="create new file" height="30px" width="30px"
                        version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 512 512" xml:space="preserve">
                        <polygon style="fill:#EEEEEE;"
                            points="405.787,449.362 405.787,117.106 330.542,62.638 373.106,449.362 " />
                        <polygon style="fill:#FFFFFF;"
                            points="373.106,449.362 373.106,117.106 296.851,8.17 51.745,8.17 51.745,449.362 " />
                        <polygon style="fill:#DCDCDC;" points="405.787,117.106 296.851,8.17 296.851,117.106 " />
                        <g>
                            <path style="fill:#B9B9B9;"
                                d="M337.702,193.362H119.83c-4.512,0-8.17-3.658-8.17-8.17s3.658-8.17,8.17-8.17h217.872
		c4.512,0,8.17,3.658,8.17,8.17S342.214,193.362,337.702,193.362z" />
                            <path style="fill:#B9B9B9;"
                                d="M337.702,236.936H119.83c-4.512,0-8.17-3.658-8.17-8.17c0-4.512,3.658-8.17,8.17-8.17h217.872
		c4.512,0,8.17,3.658,8.17,8.17C345.872,233.278,342.214,236.936,337.702,236.936z" />
                            <path style="fill:#B9B9B9;"
                                d="M228.766,280.511H119.83c-4.512,0-8.17-3.658-8.17-8.17c0-4.512,3.658-8.17,8.17-8.17h108.936
		c4.512,0,8.17,3.658,8.17,8.17C236.936,276.853,233.278,280.511,228.766,280.511z" />
                        </g>
                        <path style="fill:#02ACAB;"
                            d="M351.319,285.957V503.83c60.165,0,108.936-48.771,108.936-108.936S411.485,285.957,351.319,285.957z" />
                        <path style="fill:#42C8C6;"
                            d="M351.319,285.957c42.115,0,76.255,48.771,76.255,108.936S393.434,503.83,351.319,503.83
	c-60.165,0-108.936-48.771-108.936-108.936S291.154,285.957,351.319,285.957z" />
                        <path style="fill:#FFFFFF;"
                            d="M405.787,378.553H367.66v-38.128c0-9.024-7.316-16.34-16.34-16.34c-9.024,0-16.34,7.316-16.34,16.34
	v38.128h-38.128c-9.024,0-16.34,7.316-16.34,16.34c0,9.024,7.316,16.34,16.34,16.34h38.128v38.128c0,9.024,7.316,16.34,16.34,16.34
	c9.024,0,16.34-7.316,16.34-16.34v-38.128h38.128c9.024,0,16.34-7.316,16.34-16.34C422.128,385.869,414.811,378.553,405.787,378.553
	z" />
                        <path
                            d="M220.602,441.191H59.915V16.34c37.32,0,191.422,0,228.766,0v100.766c0,4.512,3.658,8.17,8.17,8.17h100.766v138.9
	c0,4.512,3.658,8.17,8.17,8.17c4.512,0,8.17-3.658,8.17-8.17v-147.07c0-2.167-0.861-4.245-2.393-5.777L302.628,2.393
	C301.096,0.861,299.018,0,296.851,0H54.497c-4.096,0-5.476,0-7.655,1.634c-2.058,1.544-3.268,3.964-3.268,6.536v441.191
	c0,4.512,3.658,8.17,8.17,8.17h168.858c4.512,0,8.17-3.658,8.17-8.17C228.772,444.85,225.114,441.191,220.602,441.191z
	 M305.021,27.895l81.041,81.041h-81.041V27.895z" />
                        <path
                            d="M351.319,277.787c-64.573,0-117.106,52.533-117.106,117.106S286.746,512,351.319,512s117.106-52.533,117.106-117.106
	S415.892,277.787,351.319,277.787z M351.319,495.66c-55.563,0-100.766-45.203-100.766-100.766s45.203-100.766,100.766-100.766
	s100.766,45.203,100.766,100.766S406.882,495.66,351.319,495.66z" />
                        <path
                            d="M405.787,370.383H375.83v-29.957c0-13.515-10.995-24.511-24.511-24.511c-13.516,0-24.511,10.996-24.511,24.511v29.957
	h-29.957c-13.516,0-24.511,10.996-24.511,24.511c0,13.515,10.995,24.511,24.511,24.511h29.957v29.957
	c0,13.515,10.995,24.511,24.511,24.511c13.516,0,24.511-10.996,24.511-24.511v-29.957h29.957c13.516,0,24.511-10.996,24.511-24.511
	C430.298,381.379,419.303,370.383,405.787,370.383z M405.787,403.064H367.66c-4.512,0-8.17,3.658-8.17,8.17v38.128
	c0,4.505-3.665,8.17-8.17,8.17c-4.506,0-8.17-3.666-8.17-8.17v-38.128c0-4.512-3.658-8.17-8.17-8.17h-38.128
	c-4.506,0-8.17-3.666-8.17-8.17c0-4.505,3.665-8.17,8.17-8.17h38.128c4.512,0,8.17-3.658,8.17-8.17v-38.128
	c0-4.505,3.665-8.17,8.17-8.17c4.506,0,8.17,3.666,8.17,8.17v38.128c0,4.512,3.658,8.17,8.17,8.17h38.128
	c4.506,0,8.17,3.666,8.17,8.17C413.957,399.398,410.293,403.064,405.787,403.064z" />
                    </svg>

                    <?xml version="1.0" encoding="iso-8859-1"?>
                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                    <svg id="new-folder" style="cursor:pointer" title="create new folder" height="30px" width="30px"
                        version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 512 512" xml:space="preserve">
                        <polygon style="fill:#8B8892;"
                            points="228.654,51.848 201.251,51.848 250.914,141.757 457.307,141.757 457.307,125.588 
	457.307,51.848 " />
                        <polygon style="fill:#FFB74D;"
                            points="269.993,109.424 228.656,34.574 227.546,32.569 0,32.569 0,382.026 320.278,382.026 
	457.311,382.026 457.311,275.389 457.311,270.887 457.311,109.424 " />
                        <polygon style="fill:#FFA91E;"
                            points="269.993,109.424 228.656,34.574 228.656,382.026 320.278,382.026 457.311,382.026 
	457.311,275.389 457.311,270.887 457.311,109.424 " />
                        <path style="fill:#67BFFF;"
                            d="M512,370.702c0,60.05-48.678,108.728-108.728,108.728c-0.259,0-0.517,0-0.776-0.011
	c-29.373-0.194-55.965-12.051-75.41-31.162c-17.462-17.16-29.157-40.163-31.906-65.86c-0.41-3.837-0.636-7.739-0.636-11.695
	c0-32.585,14.347-61.818,37.048-81.748c0.248-0.216,0.496-0.431,0.744-0.636c8.85-7.632,18.95-13.851,29.933-18.335
	c12.428-5.055,25.999-7.901,40.227-7.987c0.259-0.011,0.517-0.011,0.776-0.011C463.322,261.985,512,310.652,512,370.702z" />
                        <path style="fill:#0088FF;"
                            d="M512,370.702c0,60.05-48.678,108.728-108.728,108.728c-0.259,0-0.517,0-0.776-0.011V261.996
	c0.259-0.011,0.517-0.011,0.776-0.011C463.322,261.985,512,310.652,512,370.702z" />
                        <polygon style="fill:#FFFFFF;"
                            points="460.487,355.59 460.487,387.927 419.44,387.927 419.44,431.108 387.103,431.108 
	387.103,387.927 346.057,387.927 346.057,355.59 387.103,355.59 387.103,312.42 419.44,312.42 419.44,355.59 " />
                        <polygon style="fill:#D9D8DB;"
                            points="460.487,355.59 460.487,387.927 419.44,387.927 419.44,431.108 402.496,431.108 
	402.496,312.42 419.44,312.42 419.44,355.59 " />
                    </svg>
                    {{-- <input type="button" value="+ new folder" id="new-folder"> --}}
                </div>

                <table class="table table-hover table-border table-condensed">
                    <thead class="table-primary">
                        <tr>
                            <th>Files</th>
                            <th>Sizes</th>
                            <th>Modification date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pathContents as $content)
                            @if ($content['type'] === 'file')
                                <tr>

                                    <td>
                                        <?xml version="1.0" ?><svg height="24" version="1.1" width="24"
                                            xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#"
                                            xmlns:dc="http://purl.org/dc/elements/1.1/"
                                            xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
                                            <g transform="translate(0 -1028.4)">
                                                <path
                                                    d="m5 1030.4c-1.1046 0-2 0.9-2 2v8 4 6c0 1.1 0.8954 2 2 2h14c1.105 0 2-0.9 2-2v-6-4-4l-6-6h-10z"
                                                    fill="#95a5a6" />
                                                <path
                                                    d="m5 1029.4c-1.1046 0-2 0.9-2 2v8 4 6c0 1.1 0.8954 2 2 2h14c1.105 0 2-0.9 2-2v-6-4-4l-6-6h-10z"
                                                    fill="#bdc3c7" />
                                                <path d="m21 1035.4-6-6v4c0 1.1 0.895 2 2 2h4z" fill="#95a5a6" />
                                                <path
                                                    d="m6 8v1h12v-1h-12zm0 3v1h12v-1h-12zm0 3v1h12v-1h-12zm0 3v1h12v-1h-12z"
                                                    fill="#95a5a6" transform="translate(0 1028.4)" />
                                            </g>
                                        </svg> {{ $content['filename'] }}
                                    </td>
                                    <td>{{ number_format($content['size'] / 1000) }} KB</td>
                                    <td>{{ $content['last_modified'] }}</td>
                                    <td onclick="showOption({{ json_encode($content) }})">
                                        <?xml version="1.0" encoding="utf-8"?>

                                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="Menu / More_Grid_Big">
                                                <g id="Vector">
                                                    <path
                                                        d="M17 18C17 18.5523 17.4477 19 18 19C18.5523 19 19 18.5523 19 18C19 17.4477 18.5523 17 18 17C17.4477 17 17 17.4477 17 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 18C11 18.5523 11.4477 19 12 19C12.5523 19 13 18.5523 13 18C13 17.4477 12.5523 17 12 17C11.4477 17 11 17.4477 11 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 18C5 18.5523 5.44772 19 6 19C6.55228 19 7 18.5523 7 18C7 17.4477 6.55228 17 6 17C5.44772 17 5 17.4477 5 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M17 12C17 12.5523 17.4477 13 18 13C18.5523 13 19 12.5523 19 12C19 11.4477 18.5523 11 18 11C17.4477 11 17 11.4477 17 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 12C5 12.5523 5.44772 13 6 13C6.55228 13 7 12.5523 7 12C7 11.4477 6.55228 11 6 11C5.44772 11 5 11.4477 5 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5C17.4477 5 17 5.44772 17 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 6C11 6.55228 11.4477 7 12 7C12.5523 7 13 6.55228 13 6C13 5.44772 12.5523 5 12 5C11.4477 5 11 5.44772 11 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 6C5 6.55228 5.44772 7 6 7C6.55228 7 7 6.55228 7 6C7 5.44772 6.55228 5 6 5C5.44772 5 5 5.44772 5 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </g>
                                            </g>
                                        </svg>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>
                                        <a href="{{ url()->current() }}?{{$queryPath}}/{{ $content['folder_name'] }}"
                                            style="text-decoration: none">
                                            <?xml version="1.0" ?><svg height="24" version="1.1" width="24"
                                                xmlns="http://www.w3.org/2000/svg"
                                                xmlns:cc="http://creativecommons.org/ns#"
                                                xmlns:dc="http://purl.org/dc/elements/1.1/"
                                                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
                                                <g transform="translate(0 -1028.4)">
                                                    <path
                                                        d="m2 1033.4c-1.1046 0-2 0.9-2 2v14c0 1.1 0.89543 2 2 2h20c1.105 0 2-0.9 2-2v-14c0-1.1-0.895-2-2-2h-20z"
                                                        fill="#2980b9" />
                                                    <path
                                                        d="m3 1029.4c-1.1046 0-2 0.9-2 2v14c0 1.1 0.8954 2 2 2h11 5 2c1.105 0 2-0.9 2-2v-9-3c0-1.1-0.895-2-2-2h-2-5-1l-3-2h-7z"
                                                        fill="#2980b9" />
                                                    <path
                                                        d="m23 1042.4v-8c0-1.1-0.895-2-2-2h-11-5-2c-1.1046 0-2 0.9-2 2v8h22z"
                                                        fill="#bdc3c7" />
                                                    <path
                                                        d="m2 1033.4c-1.1046 0-2 0.9-2 2v6 1 6c0 1.1 0.89543 2 2 2h20c1.105 0 2-0.9 2-2v-6-1-6c0-1.1-0.895-2-2-2h-20z"
                                                        fill="#3498db" />
                                                </g>
                                            </svg> {{ $content['folder_name'] }}
                                        </a>
                                    </td>

                                    <td> - </td>
                                    <td> - </td>
                                    <td onclick="showFolderOption({{ json_encode($content) }})">
                                        <?xml version="1.0" encoding="utf-8"?>

                                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="Menu / More_Grid_Big">
                                                <g id="Vector">
                                                    <path
                                                        d="M17 18C17 18.5523 17.4477 19 18 19C18.5523 19 19 18.5523 19 18C19 17.4477 18.5523 17 18 17C17.4477 17 17 17.4477 17 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 18C11 18.5523 11.4477 19 12 19C12.5523 19 13 18.5523 13 18C13 17.4477 12.5523 17 12 17C11.4477 17 11 17.4477 11 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 18C5 18.5523 5.44772 19 6 19C6.55228 19 7 18.5523 7 18C7 17.4477 6.55228 17 6 17C5.44772 17 5 17.4477 5 18Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M17 12C17 12.5523 17.4477 13 18 13C18.5523 13 19 12.5523 19 12C19 11.4477 18.5523 11 18 11C17.4477 11 17 11.4477 17 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 12C5 12.5523 5.44772 13 6 13C6.55228 13 7 12.5523 7 12C7 11.4477 6.55228 11 6 11C5.44772 11 5 11.4477 5 12Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5C17.4477 5 17 5.44772 17 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M11 6C11 6.55228 11.4477 7 12 7C12.5523 7 13 6.55228 13 6C13 5.44772 12.5523 5 12 5C11.4477 5 11 5.44772 11 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path
                                                        d="M5 6C5 6.55228 5.44772 7 6 7C6.55228 7 7 6.55228 7 6C7 5.44772 6.55228 5 6 5C5.44772 5 5 5.44772 5 6Z"
                                                        stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </g>
                                            </g>
                                        </svg>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <div id="context-menu">
        <div class="item" id="download">Download</div>
        <div class="item" id="view">View</div>
        <div class="item border-bottom " id="edit">Edit</div>
        <div class="item" id="move">Move</div>
        <div class="item border-bottom" id="copy">Copy</div>
        {{-- <div class="item border-bottom">Paste</div> --}}
        <div class="item" id="rename">Rename</div>
        {{-- <div class="item border-bottom">Achive</div> --}}
        <div class="item" id="delete">Delete</div>
    </div>


    <div id="context-folder-menu">
        <div class="item" id="rename">Rename</div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">View Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="body-view-div" class="p-1" style="overflow:auto;"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Edit Content</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="body-edit-div" class="p-1" style="overflow:auto;">
                        <form id="edit-form" action="{{ route('files.store') }}" method="post">
                            @csrf
                            <div id="store-div"></div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="CreateDirectoryModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Create New Directory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('files.create.directory')}}" method="post" id="newDirectoryForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" class="form-control p-3" name="new-directory-name" id="createDirId" required>
                                <input type="hidden" name="path" value="{{ str_replace('\\', '~', $path .'\\'. $params) }}" id="path-id">
                                
                            </div>
                            <div class="col-md-3">
                                <input type="submit" class="btn btn-secondary" value="Create" disabled id="dirSubmit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="CreateFileModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Create New File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('files.create.file')}}" method="post" id="newFileForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" placeholder="include ext.  e.g index.php, file1.txt"
                                    class="form-control p-3" name="new-file-name" id="createFileId" required>

                                <input type="hidden" name="path" value="{{ str_replace('\\', '~', $path .'\\'. $params) }}" id="path-id">
                            </div>
                            <div class="col-md-3">
                                <input type="submit" class="btn btn-secondary" disabled value="Create" id="fileSubmit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renameFileModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Rename File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('files.rename.file')}}" method="post" id="renameFileForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" placeholder="enter new name" class="form-control p-3"
                                    name="rename-file-name" id="renameFileId" required>
                                <input type="hidden" name="content" id="content-id">
                            </div>
                            <div class="col-md-3">
                                <input type="submit" class="btn btn-secondary" value="Rename" id="renameSubmit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="copyFileModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Copy File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('files.copy')}}" method="post" id="copyFileForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" placeholder="enter file path" class="form-control p-3"
                                    name="copy-file-path" id="copyFilePathId" required>
                                    <input type="hidden" id="copy-value-content" name="content">
                            </div>
                            <div class="col-md-3">
                                <input type="submit" class="btn btn-secondary" value="Copy" id="copySubmit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="moveFileModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mysqlresetModalLabel">Move File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('files.move')}}" method="post" id="moveFileForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text" placeholder="enter file path" class="form-control p-3"
                                    name="move-file-path" id="moveFilePathId" required>
                                <input type="hidden" id="value-content" name="content">
                            </div>
                            <div class="col-md-3">
                                <input type="submit" class="btn btn-secondary" value="Move" id="moveSubmit">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('js')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var valueContent;

        function showOption(content) {
            valueContent = content;
            optionView('context-menu');
        }

        // console.log(JSON.stringify(content));
        $('#view').on('click', function(e) {
            e.preventDefault();

            console.log(JSON.stringify(valueContent));

            $.ajax({
                url: '{{ route('files.show') }}',
                type: 'POST',
                data: JSON.stringify(valueContent),
                success: function(data) {
                    console.log(data);
                    $('#context-menu').hide();

                    if (typeof(data) == 'object' && Object.keys(data)[0] == 'nonmedia') {
                        $('#body-view-div').html(' ');
                        var text =
                            `<textarea readonly disabled class="form-control" name="" id="textarea-id" cols="30" rows="25" style="border: 2px solid black;background-color:#ccc;">${data['nonmedia']}</textarea>`;
                        $('#body-view-div').html(text);
                        $('#viewModal').modal('show');
                    } else {
                        window.open(data, '_blank');
                    }

                },
                error: function(data) {
                    console.log(data);
                }
            })
        });

        $('#download').on('click', function() {
            $.ajax({
                url: '{{ route('files.download') }}',
                type: 'POST',
                data: JSON.stringify(valueContent),
                success: function(data) {
                    console.log(data);
                    $('#context-menu').hide();
                    window.open(data, '_blank');
                },
                error: function(data) {}
            })
        });


        $('#edit').on('click', function() {
            $.ajax({
                url: '{{ route('files.edit') }}',
                type: 'POST',
                data: JSON.stringify(valueContent),
                success: function(data) {
                    console.log(data);
                    $('#context-menu').hide();
                    var text = `
                        <input type="hidden" value='${JSON.stringify(valueContent)}' name="content">
                        <textarea class="form-control" name="data" id="" cols="30" rows="25" style="border: 2px solid black; background-color:#eee">${data}</textarea>
                        <div class="mx-1 mt-2 text-right">
                            <input type="button" data-dismiss="modal" value="cancel" class="btn btn-danger">
                            <input type="submit" class="btn btn-success" value="save">
                           
                        </div>
                        
                        `;
                    $('#store-div').html(text);
                    $('#editModal').modal('show');
                },
                error: function(data) {

                }

            })
        });

        $('#rename').on('click', function() {
            $('#renameFileModal').modal('show');
            var oldFName = valueContent.filename;

            $('#renameFileId').val(oldFName);
            $('#content-id').val(JSON.stringify(valueContent));
            $('#context-menu').hide();

            console.log(JSON.stringify(valueContent));

            // $('#renameFileForm').on('submit', (e) => {
            //     e.preventDefault();

            //     $.ajax({
            //         url: '{{ route('files.rename.file') }}',
            //         type: 'POST',
            //         data: JSON.stringify({
            //             "content": JSON.stringify(valueContent),
            //             "newName": $('#renameFileId').val()
            //         }),
            //         dataType: "json",
            //         success: function(data) {
            //             console.log(data);
            //             window.location.reload();


            //         },
            //         error: function(data) {

            //         }

            //     })

            // })
        });


        //Copy
        $('#copy').on('click', function() {
            $('#copyFileModal').modal('show');
            var path = valueContent.pathName;

            $('#copyFilePathId').val(path.substring(0, path.lastIndexOf("\\")));
            $('#copy-value-content').val(JSON.stringify(valueContent))
            $('#context-menu').hide();

            // console.log(JSON.stringify(valueContent));

            // $('#copySubmit').on('click', (e) => {
            //     e.preventDefault();

            //     $.ajax({
            //         url: '{{ route('files.copy') }}',
            //         type: 'POST',
            //         data: JSON.stringify({
            //             "content": JSON.stringify(valueContent),
            //             "copyPath": $('#copyFileId').val()
            //         }),
            //         dataType: "json",
            //         success: function(data) {
            //             console.log(data);
            //             window.location.reload();

            //         },
            //         error: function(data) {}
            //     })

            // })
        });


        //move
        $('#move').on('click', function() {
            $('#moveFileModal').modal('show');
            var path = valueContent.pathName;

            $('#moveFilePathId').val(path.substring(0, path.lastIndexOf("\\")));
            $('#value-content').val(JSON.stringify(valueContent))
            $('#context-menu').hide();
            
            // $('#moveSubmit').on('click', (e) => {
            //     e.preventDefault();

            //     $.ajax({
            //         url: '{{ route('files.move') }}',
            //         type: 'POST',
            //         data: JSON.stringify({
            //             "content": JSON.stringify(valueContent),
            //             "movePath": $('#moveFileId').val()
            //         }),
            //         dataType: "json",
            //         success: function(data) {
            //             console.log(data);
            //             window.location.reload();

            //         },
            //         error: function(data) {}
            //     })

            // })
        });


        //delete
        $('#delete').on('click', function() {
            $.ajax({
                url: '{{ route('files.delete') }}',
                type: 'POST',
                data: JSON.stringify(valueContent),
                success: function(data) {
                    console.log(data);
                    $('#context-menu').hide();
                    window.location.reload();
                },
                error: function(data) {

                }
            })
        })


        function optionView(menuContext) {
            $('#context-menu').show();
            const contextMenu = document.getElementById(menuContext);
            const scope = document.querySelector("table tbody");
            const normalizePozition = (mouseX, mouseY) => {
                // ? compute what is the mouse position relative to the container element (scope)
                let {
                    left: scopeOffsetX,
                    top: scopeOffsetY,
                } = scope.getBoundingClientRect();

                scopeOffsetX = scopeOffsetX < 0 ? 0 : scopeOffsetX;
                scopeOffsetY = scopeOffsetY < 0 ? 0 : scopeOffsetY;

                const scopeX = mouseX - scopeOffsetX;
                const scopeY = mouseY - scopeOffsetY;

                // ? check if the element will go out of bounds
                const outOfBoundsOnX =
                    scopeX + contextMenu.clientWidth > scope.clientWidth;

                const outOfBoundsOnY =
                    scopeY + contextMenu.clientHeight > scope.clientHeight;

                let normalizedX = mouseX;
                let normalizedY = mouseY;

                // ? normalize on X
                if (outOfBoundsOnX) {
                    normalizedX =
                        scopeOffsetX + scope.clientWidth - contextMenu.clientWidth;
                }

                // ? normalize on Y
                if (outOfBoundsOnY) {
                    normalizedY =
                        scopeOffsetY + scope.clientHeight - contextMenu.clientHeight;
                }

                return {
                    normalizedX,
                    normalizedY
                };
            };

            // scope.addEventListener("contextmenu", (event) => {
            //     event.preventDefault();

            const {
                clientX: mouseX,
                clientY: mouseY
            } = event;

            const {
                normalizedX,
                normalizedY
            } = normalizePozition(mouseX, mouseY);

            contextMenu.classList.remove("visible");

            contextMenu.style.top = `${normalizedY}px`;
            contextMenu.style.left = `${normalizedX}px`;

            setTimeout(() => {
                contextMenu.classList.add("visible");
            });
            // });

            scope.addEventListener("click", (e) => {
                // ? close the menu if the user clicks outside of it
                if (e.target.offsetParent != contextMenu) {
                    contextMenu.classList.remove("visible");
                }

            });

            $(window).on("scroll", function() {
                contextMenu.classList.remove("visible");
            });

        }

        $('#new-folder').on('click', () => {
            $('#CreateDirectoryModal').modal('show');
            // const dirPath = "{{ str_replace('\\', ' ', $path) }}";

            // $('#newDirectoryForm').on('submit', (e) => {
            //     e.preventDefault();
            //     $.ajax({
            //         url: '{{ route('files.create.directory') }}',
            //         type: 'POST',
            //         data: JSON.stringify({
            //             path: dirPath,
            //             name: $('#createDirId').val()
            //         }),
            //         dataType: 'json',
            //         success: function(data) {
            //             console.log(data);
            //             window.location.reload();
            //         },
            //         error: function(data) {

            //         }
            //     })
            // })
        });


        $('#new-file').on('click', () => {
            $('#CreateFileModal').modal('show');
            const dirPath = "{{ str_replace('\\', ' ', $path) }}";
          
            // $('#newFileForm').on('submit', (e) => {
            //     e.preventDefault();
            //     $.ajax({
            //         url: '{{ route('files.create.file') }}',
            //         type: 'POST',
            //         data: JSON.stringify({
            //             path: dirPath,
            //             name: $('#createFileId').val()
            //         }),
            //         dataType: 'json',
            //         success: function(data) {
            //             console.log(data);
            //             window.location.reload();
            //         },
            //         error: function(data) {

            //         }
            //     })
            // })
        });

        $('#createDirId').keyup((event) => {
            if (event.key === "Backspace" || event.key === "Delete") {
                if ($('#createDirId').val().length <= 1) {
                    $('#dirSubmit').attr('disabled', true);
                }
            } else {
                if ($('#createDirId').val().length > 1) {
                    $('#dirSubmit').removeAttr('disabled');
                }
            }

        });



        $('#createFileId').keyup((event) => {
            if (event.key === "Backspace" || event.key === "Delete") {
                if ($('#createFileId').val().length <= 1) {
                    $('#fileSubmit').attr('disabled', true);
                }
            } else {
                if ($('#createFileId').val().length > 1) {
                    $('#fileSubmit').removeAttr('disabled');
                }
            }

        });



        $('#renameFileId').keyup((event) => {
            if (event.key === "Backspace" || event.key === "Delete") {
                if ($('#renameFileId').val().length <= 1) {
                    $('#renameSubmit').attr('disabled', true);
                }
            } else {
                if ($('#renameFileId').val().length > 1) {
                    $('#renameSubmit').removeAttr('disabled');
                }
            }

        });


        $('#copyFileId').keyup((event) => {
            if (event.key === "Backspace" || event.key === "Delete") {
                if ($('#copyFileId').val().length <= 1) {
                    $('#copySubmit').attr('disabled', true);
                }
            } else {
                if ($('#copyFileId').val().length > 1) {
                    $('#copySubmit').removeAttr('disabled');
                }
            }

        });


        $('#moveFileId').keyup((event) => {
            if (event.key === "Backspace" || event.key === "Delete") {
                if ($('#moveFileId').val().length <= 1) {
                    $('#moveSubmit').attr('disabled', true);
                }
            } else {
                if ($('#moveFileId').val().length > 1) {
                    $('#moveSubmit').removeAttr('disabled');
                }
            }

        });


        function showFolderOption(content) {
            optionView('context-folder-menu');
        }
    </script>
@endsection
