@extends('layouts.app')

@section('title', 'ติดตามคำขอ | SCi-Safety')

@section('content')
    @include('requests.partials.list', [
        'title' => 'ติดตามคำขอ',
        'lead' => 'แสดงเฉพาะคำขอของบัญชีนี้',
        'empty' => 'ยังไม่มีคำขอ เริ่มได้จากปุ่มแจ้งขอความช่วยเหลือ',
    ])
@endsection
