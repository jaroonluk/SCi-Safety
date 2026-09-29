@extends('layouts.app')

@section('title', 'กำหนดสิทธิ์')

@section('content')
    <section class="card page">
        <h1>กำหนดสิทธิผู้ใช้งาน</h1>
        <p>ผู้ใช้ต้องเข้าสู่ระบบด้วย Google อย่างน้อยหนึ่งครั้ง จึงจะปรากฏในรายการนี้</p>
        @if (session('error')) <p class="alert">{{ session('error') }}</p> @endif
        <table>
            <tr><th>ชื่อ</th><th>อีเมล</th><th>สิทธิ์ปัจจุบัน</th><th>กำหนดใหม่</th></tr>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role->label() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf
                            <select name="role">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-solid" type="submit">บันทึก</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </section>
    <section class="card page">
        <h2>เจ้าหน้าที่สำรอง</h2>
        <p>ในช่วงวันที่กำหนด เจ้าหน้าที่สำรองจะเห็นคิวงานเดียวกับเจ้าหน้าที่ผู้รับผิดชอบคำขอ</p>
        <form method="POST" action="{{ route('admin.covers.store') }}">
            @csrf
            <div class="grid-2">
                <label class="field">เจ้าหน้าที่หลัก
                    <select name="officer_user_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">ผู้ทำหน้าที่แทน
                    <select name="backup_user_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">เริ่ม<input type="date" name="starts_on" required></label>
                <label class="field">สิ้นสุด<input type="date" name="ends_on" required></label>
                <label class="field">พื้นที่<input name="area"></label>
            </div>
            <button class="btn btn-solid" type="submit">กำหนดผู้แทน</button>
        </form>
    </section>
@endsection
