@extends('layouts.app')

@section('title', 'ยื่นคำขอ | SCi-Safety')

@section('content')
    @php
        $openStep = 1;
        if ($errors->hasAny(['incident_type', 'purpose', 'started_at', 'ended_at', 'building', 'location', 'urgency', 'details', 'external_organization', 'official_letter_no', 'external_contact_name', 'subject_name', 'subject_code', 'exam_room'])) {
            $openStep = 2;
        }
        if ($errors->hasAny(['privacy_accepted', 'privacy_notice_version'])) {
            $openStep = 3;
        }
        $buildings = ['อาคารวิทยพัฒนา', 'อาคารจุลชีววิทยา', 'อาคารเคมี', 'อาคารฟิสิกส์', 'อาคารชีววิทยา', 'อาคารคณิตศาสตร์', 'อาคารสถิติ', 'อาคารบรรยายรวม', 'อาคารปฏิบัติการพื้นฐาน'];
        $zones = ['โถงทางเดิน', 'หน้าห้องเรียน', 'ห้องสอบ', 'บันได', 'ลานจอดรถ', 'ประตูเข้าอาคาร', 'ลานกิจกรรม'];
    @endphp
    <noscript><style>[data-step]{display:block !important}</style></noscript>
    <form class="card page" id="wizard" method="POST" action="{{ route('requests.store') }}" data-open="{{ $openStep }}">
        @csrf
        <a class="back" href="{{ route('home') }}" style="color:var(--accent);font-weight:700;text-decoration:none">กลับหน้าหลัก</a>
        <h1>แจ้งเรื่องที่ต้องการให้ช่วยดู</h1>
        <p class="lead" style="color:var(--muted)">แบ่งเป็น 3 ขั้นสั้น ๆ ท่านพักระหว่างขั้นได้ ระบบยังไม่ส่งจนกว่าจะถึงขั้นสุดท้าย</p>
        <div class="reassure"><x-icon name="shield" /> <span>คณะวิทยาศาสตร์ให้ความสำคัญกับความปลอดภัยและข้อมูลส่วนบุคคลของท่าน เจ้าหน้าที่จะเริ่มตรวจสอบข้อมูลภายใน 24 ชั่วโมงทำการ</span></div>
        <div class="wizard-dots" aria-hidden="true">
            <span data-step-dot="1" class="{{ $openStep === 1 ? 'is-current' : '' }}"></span>
            <span data-step-dot="2"></span>
            <span data-step-dot="3"></span>
        </div>

        <section data-step="1" @if($openStep !== 1) hidden @endif>
            <h2>ขั้นที่ 1 · แนะนำตัว</h2>
            <p>ชื่อและอีเมลมาจากบัญชีที่ท่านใช้เข้าสู่ระบบแล้ว เมื่อเชื่อมต่อระบบมหาวิทยาลัย สังกัดจะถูกเติมให้โดยอัตโนมัติ</p>
            <div class="grid-2">
                <p><strong>{{ auth()->user()->name }}</strong><br>{{ auth()->user()->email }}</p>
                <p>{{ auth()->user()->account_type->label() }}</p>
            </div>
            <div class="grid-2">
                <label>ท่านยื่นในฐานะ
                    <select name="requester_kind" required>
                        @foreach (['student_sci' => 'นักศึกษาคณะวิทยาศาสตร์', 'student_other' => 'นักศึกษาต่างคณะ', 'staff_sci' => 'บุคลากรคณะวิทยาศาสตร์', 'staff_kku' => 'บุคลากรหน่วยงานอื่นใน มข.', 'external_person' => 'บุคคลภายนอก', 'external_org' => 'ผู้แทนหน่วยงานภายนอก'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('requester_kind') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>รหัสนักศึกษาหรือบุคลากร ถ้ามี
                    <input name="identifier" value="{{ old('identifier') }}">
                </label>
                <label>สังกัดหรือคณะ
                    <input name="affiliation" value="{{ old('affiliation') }}">
                </label>
                <label>เบอร์ที่ติดต่อได้
                    <input name="phone" value="{{ old('phone') }}" required>
                    @error('phone') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>
            <button class="btn btn-solid" type="button" data-next>ถัดไป เล่าเหตุการณ์</button>
        </section>

        <section data-step="2" @if($openStep !== 2) hidden @endif>
            <h2>ขั้นที่ 2 · เกิดอะไรขึ้น</h2>
            <label class="field">ประเภทเหตุการณ์
                <select name="incident_type" required>
                    <option value="">เลือกประเภทที่ใกล้เคียง</option>
                    @foreach ($incidentTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('incident_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">ท่านต้องการให้ช่วยเรื่องใด
                <input name="purpose" value="{{ old('purpose') }}" maxlength="500" required>
            </label>
            <div class="grid-2">
                <label class="field">เริ่มประมาณ
                    <input type="datetime-local" name="started_at" value="{{ old('started_at') }}" required>
                </label>
                <label class="field">ถึงประมาณ
                    <input type="datetime-local" name="ended_at" value="{{ old('ended_at') }}" required>
                </label>
            </div>
            <p>เลือกอาคารที่จำได้ หากไม่มีในรายการ ให้เลือกอาคารอื่นแล้วพิมพ์ชื่อเอง</p>
            <div class="choice-grid" data-choice-for="building">
                @foreach ($buildings as $building)
                    <button class="choice" type="button" data-value="{{ $building }}">{{ $building }}</button>
                @endforeach
                <button class="choice" type="button" data-value="">อาคารอื่น</button>
            </div>
            <label class="field">ชื่ออาคาร
                <input id="building" name="building" value="{{ old('building') }}" required>
            </label>
            <p>ชั้น</p>
            <div class="choice-grid" data-choice-for="floor">
                @foreach (['ชั้นล่าง', '1', '2', '3', '4', '5', '6', '7', '8', 'ดาดฟ้า'] as $floor)
                    <button class="choice" type="button" data-value="{{ $floor }}">{{ $floor }}</button>
                @endforeach
            </div>
            <label class="field">ชั้น
                <input id="floor" name="floor" value="{{ old('floor') }}">
            </label>
            <p>บริเวณที่เกิดเหตุ</p>
            <div class="choice-grid" data-choice-for="location">
                @foreach ($zones as $zone)
                    <button class="choice" type="button" data-value="{{ $zone }}">{{ $zone }}</button>
                @endforeach
            </div>
            <label class="field">ห้องหรือจุดที่จำได้
                <input id="location" name="location" value="{{ old('location') }}" required>
            </label>
            <label class="field">จุดสังเกต เช่น สีกระเป๋า หรือหมายเลขรถ
                <input name="landmark" value="{{ old('landmark') }}">
            </label>
            <label class="field">ความเร่งด่วน
                <select name="urgency" required>
                    <option value="normal" @selected(old('urgency', 'normal') === 'normal')>รอได้ตามคิว</option>
                    <option value="urgent" @selected(old('urgency') === 'urgent')>เร่งด่วน โปรดดูโดยเร็ว</option>
                </select>
            </label>
            <div class="grid-2">
                <label class="field">หน่วยงานภายนอก ถ้ามี<input name="external_organization" value="{{ old('external_organization') }}"></label>
                <label class="field">เลขที่หนังสือ ถ้ามี<input name="official_letter_no" value="{{ old('official_letter_no') }}"></label>
                <label class="field">ผู้ประสานงาน<input name="external_contact_name" value="{{ old('external_contact_name') }}"></label>
                <label class="field">วิชา<input name="subject_name" value="{{ old('subject_name') }}"></label>
                <label class="field">รหัสวิชา<input name="subject_code" value="{{ old('subject_code') }}"></label>
                <label class="field">ห้องสอบ<input name="exam_room" value="{{ old('exam_room') }}"></label>
            </div>
            <label class="field">เล่าเหตุการณ์เท่าที่จำได้
                <textarea name="details" maxlength="2000" required>{{ old('details') }}</textarea>
            </label>
            @if ($onBehalf)
                <label class="check"><input type="checkbox" name="on_behalf" value="1" @checked(old('on_behalf'))> <span>บันทึกคำขอแทนผู้แจ้ง</span></label>
                <label class="field">ช่องทางที่รับเรื่อง<input name="intake_channel" value="{{ old('intake_channel') }}"></label>
                <label class="field">ชื่อผู้แจ้งตัวจริง<input name="informant_name" value="{{ old('informant_name') }}"></label>
                <label class="field">เหตุผลที่บันทึกแทน<textarea name="on_behalf_reason">{{ old('on_behalf_reason') }}</textarea></label>
            @endif
            <button class="btn btn-quiet" type="button" data-back>ย้อนกลับ</button>
            <button class="btn btn-solid" type="button" data-next>ถัดไป อ่านข้อตกลง</button>
        </section>

        <section data-step="3" @if($openStep !== 3) hidden @endif>
            <h2>ขั้นที่ 3 · สิ่งที่ควรรู้ก่อนส่ง</h2>
            <div class="card" style="padding:1rem;box-shadow:none">
                <ul>
                    <li>ระบบนี้รับคำขอและนัดดูภาพต่อหน้าเจ้าหน้าที่เท่านั้น</li>
                    <li>ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ และไม่มีการบันทึกภาพหน้าจอ</li>
                    <li>จะดูเฉพาะจุดและช่วงเวลาที่เกี่ยวข้องกับเรื่องของท่าน</li>
                    <li>ท่านขอดูหรือแก้ไขข้อมูลของท่านได้โดยติดต่อเจ้าหน้าที่ผู้รับเรื่อง</li>
                </ul>
                <p><a href="{{ route('privacy.show') }}" target="_blank">อ่านประกาศความเป็นส่วนตัวฉบับเต็ม รุ่น {{ $notice->version }}</a></p>
            </div>
            <input type="hidden" name="privacy_notice_version" value="{{ $notice->version }}">
            <label class="check" style="margin:1rem 0;padding:0.9rem;border:1px solid var(--accent);border-radius:12px;background:var(--info-bg)">
                <input type="checkbox" name="privacy_accepted" value="1" @checked(old('privacy_accepted')) required>
                <span>ข้าพเจ้าได้อ่านและรับทราบประกาศความเป็นส่วนตัวและเงื่อนไขการเข้าตรวจดูภาพภายใต้การควบคุมของเจ้าหน้าที่แล้ว</span>
            </label>
            @error('privacy_accepted') <p class="error">กรุณารับทราบประกาศความเป็นส่วนตัวก่อนส่งคำขอ</p> @enderror
            <button class="btn btn-quiet" type="button" data-back>ย้อนกลับ</button>
            <button class="btn btn-solid" type="submit">ส่งคำขอ</button>
        </section>
    </form>
    <script>
        const form = document.getElementById('wizard');
        const steps = [...form.querySelectorAll('[data-step]')];
        let current = Number(form.dataset.open || 1);
        const show = (n) => {
            current = n;
            steps.forEach((step) => { step.hidden = Number(step.dataset.step) !== n; });
            form.querySelectorAll('[data-step-dot]').forEach((dot) => {
                const index = Number(dot.dataset.stepDot);
                dot.classList.toggle('is-current', index === n);
                dot.classList.toggle('is-done', index < n);
            });
        };
        show(current);
        form.querySelectorAll('[data-next]').forEach((button) => button.addEventListener('click', () => {
            const fields = steps.find((step) => Number(step.dataset.step) === current).querySelectorAll('input, select, textarea');
            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return;
                }
            }
            show(current + 1);
        }));
        form.querySelectorAll('[data-back]').forEach((button) => button.addEventListener('click', () => show(current - 1)));
        form.querySelectorAll('[data-choice-for]').forEach((group) => {
            const input = document.getElementById(group.dataset.choiceFor);
            group.querySelectorAll('[data-value]').forEach((choice) => choice.addEventListener('click', () => {
                group.querySelectorAll('.choice').forEach((item) => item.classList.remove('is-on'));
                choice.classList.add('is-on');
                if (choice.dataset.value !== '') {
                    input.value = choice.dataset.value;
                }
                input.focus();
            }));
        });
    </script>
@endsection
