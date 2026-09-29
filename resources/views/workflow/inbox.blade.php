@extends('layouts.app')

@section('title', $title.' | SCi-Safety')

@section('content')
    @include('requests.partials.list', [
        'title' => $title,
        'lead' => 'เปิดเรื่องเพื่ออ่านสรุป แล้วกดคำวินิจฉัยได้จากด้านล่างของหน้า',
        'empty' => 'ยังไม่มีเรื่องรอท่านอยู่ในขณะนี้',
    ])
@endsection