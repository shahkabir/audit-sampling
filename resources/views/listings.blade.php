<h1>{{$heading}}</h1>
@foreach ($listings as $list)
    <h2>
        <a href="/single/{{$list['id']}}">{{$list['title']}}</a>
    </h2>
    <p>{{$list['desc']}}</p>
@endforeach


@php
$i='10';
@endphp

{{$i}}
