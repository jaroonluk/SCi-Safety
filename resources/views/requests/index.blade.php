@extends('layouts.app')

@section('title', 'ติดตามคำขอและนัดดูภาพ | SCi-Safety')

@section('content')
    @include('requests.partials.list', [
        'title' => 'ติดตามคำขอและนัดดูภาพ',
        'lead' => 'ดูสถานะคำขอของบัญชีนี้ รวมถึงวัน เวลา และห้องควบคุมเมื่อได้รับนัดดูภาพ',
        'empty' => 'ยังไม่มีคำขอ เริ่มได้จากปุ่มแจ้งเรื่องที่ต้องการดูภาพ',
    ])
@endsection
