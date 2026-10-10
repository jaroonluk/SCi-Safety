@extends('layouts.app')

@section('title', 'กำหนดสิทธิ์')

@section('content')
    @if ($impersonationEnabled)
        <section class="card page">
            <h1>ทดสอบระบบตามสิทธิ์</h1>
            <p>เลือกสิทธิ์ด้านล่างเพื่อเข้าใช้งานระบบในมุมมองนั้นทันที ไม่ต้องมีผู้ใช้จริงในแต่ละสิทธิ์ เมื่อทดสอบเสร็จให้ตั้ง <code>IMPERSONATION_ENABLED=false</code></p>
            <div class="role-test-grid">
                @foreach ($roles as $role)
                    <form method="POST" action="{{ route('admin.impersonate.role') }}" class="role-test-card">
                        @csrf
                        <input type="hidden" name="role" value="{{ $role->value }}">
                        <strong>{{ $role->label() }}</strong>
                        <span>{{ $role->value }}</span>
                        <button class="btn btn-solid" type="submit">เข้าใช้งานด้วยสิทธิ์นี้</button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif

    <section class="card page">
        <h1>กำหนดสิทธิผู้ใช้งาน</h1>
        <p>ผู้ใช้ต้องเข้าสู่ระบบด้วย Google อย่างน้อยหนึ่งครั้ง จึงจะปรากฏในรายการนี้</p>
        @if (session('error')) <p class="alert">{{ session('error') }}</p> @endif
        @if ($impersonationEnabled)
            <p class="alert" style="background:var(--warn-bg);color:var(--warn)">
                หากต้องการทดสอบกับบัญชีจริงเป็นรายคน ใช้ปุ่ม <strong>เข้าใช้งานแทน</strong> ในตารางด้านล่าง
            </p>
        @endif
        <table>
            <tr>
                <th>ชื่อ</th>
                <th>อีเมล</th>
                <th>สิทธิ์ปัจจุบัน</th>
                <th>กำหนดใหม่</th>
                @if ($impersonationEnabled)
                    <th>ทดสอบบัญชี</th>
                @endif
            </tr>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->realRole()->label() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf
                            <select name="role">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected($user->realRole() === $role)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-solid" type="submit">บันทึก</button>
                        </form>
                    </td>
                    @if ($impersonationEnabled)
                        <td>
                            @if ($user->is(auth()->user()))
                                <span style="color:var(--muted)">บัญชีปัจจุบัน</span>
                            @else
                                <form method="POST" action="{{ route('admin.impersonate', $user) }}">
                                    @csrf
                                    <button class="btn btn-quiet" type="submit">เข้าใช้งานแทน</button>
                                </form>
                            @endif
                        </td>
                    @endif
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

@push('styles')
<style>
    .role-test-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.85rem;
        margin-top: 1rem;
    }
    .role-test-card {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        align-items: flex-start;
        margin: 0;
        padding: 1rem;
        border: 1px solid var(--line);
        border-radius: var(--radius);
        background: var(--sand);
    }
    .role-test-card strong { font-size: 1rem; line-height: 1.35; }
    .role-test-card span { color: var(--muted); font-size: 0.85rem; }
    .role-test-card .btn { margin-top: 0.5rem; width: 100%; }
</style>
@endpush
