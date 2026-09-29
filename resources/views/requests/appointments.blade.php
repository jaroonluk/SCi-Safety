@extends('layouts.app')

@section('title', 'นัดหมายดูภาพ | SCi-Safety')

@section('content')
    @include('requests.partials.list', [
        'title' => 'นัดหมายดูภาพ',
        'lead' => 'เมื่อเจ้าหน้าที่ตรวจสอบแล้วจะนัดวัน เวลา และสถานที่ดูภาพภายใต้การควบคุม ขณะนี้คำขอที่ส่งแล้วยังรอการนัดหมาย',
        'empty' => 'ยังไม่มีคำขอให้นัดหมาย ยื่นคำขอก่อน แล้วกลับมาดูสถานะที่นี่',
    ])
@endsection
