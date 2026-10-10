@extends('layouts.app')

@section('title', 'แจ้งเรื่องที่ต้องการดูภาพ | SCi-Safety')

@section('content')
    @php
        $openStep = 1;
        if ($errors->hasAny(['incident_type', 'purpose', 'started_at', 'ended_at', 'building', 'location', 'urgency', 'details', 'external_contact_name'])) {
            $openStep = 2;
        }
        if ($errors->hasAny(['privacy_accepted', 'privacy_notice_version'])) {
            $openStep = 3;
        }
        $zones = [
            ['label' => 'โถงทางเดิน', 'icon' => 'route'],
            ['label' => 'หน้าห้องเรียน', 'icon' => 'door'],
            ['label' => 'ห้องสอบ', 'icon' => 'document'],
            ['label' => 'บันได', 'icon' => 'stairs'],
            ['label' => 'ลานจอดรถ', 'icon' => 'pin'],
            ['label' => 'ประตูเข้าอาคาร', 'icon' => 'home'],
            ['label' => 'ลานกิจกรรม', 'icon' => 'users'],
        ];
        $catalog = $buildingCatalog ?? [];
        $stepMeta = [
            1 => ['icon' => 'person', 'title' => 'แนะนำตัว', 'hint' => 'ข้อมูลผู้ยื่น'],
            2 => ['icon' => 'camera', 'title' => 'เกิดอะไรขึ้น', 'hint' => 'เหตุการณ์และสถานที่'],
            3 => ['icon' => 'shield', 'title' => 'ข้อตกลง', 'hint' => 'ยืนยันก่อนส่ง'],
        ];
    @endphp
    <noscript><style>[data-step]{display:block !important}</style></noscript>
    <form class="card page create-form" id="wizard" method="POST" action="{{ route('requests.store') }}" data-open="{{ $openStep }}">
        @csrf
        <a class="back create-back" href="{{ route('home') }}"><x-icon name="arrow" class="icon create-back-icon" /> กลับหน้าหลัก</a>

        <header class="create-hero">
            <span class="create-hero-icon" aria-hidden="true"><x-icon name="eye" /></span>
            <div>
                <h1>แจ้งเรื่องที่ต้องการดูภาพ</h1>
                <p class="lead">แบ่งเป็น 3 ขั้นสั้น ๆ ท่านพักระหว่างขั้นได้ ระบบยังไม่ส่งจนกว่าจะถึงขั้นสุดท้าย</p>
            </div>
        </header>

        <div class="reassure"><x-icon name="shield" /> <span>คณะวิทยาศาสตร์ให้ความสำคัญกับความปลอดภัยและข้อมูลส่วนบุคคลของท่าน เจ้าหน้าที่จะเริ่มตรวจสอบข้อมูลภายใน 24 ชั่วโมงทำการ</span></div>

        <nav class="wizard-track" aria-label="ขั้นตอนการแจ้งเรื่อง">
            @foreach ($stepMeta as $n => $meta)
                <div class="wizard-step {{ $openStep === $n ? 'is-current' : '' }} {{ $openStep > $n ? 'is-done' : '' }}" data-step-dot="{{ $n }}">
                    <span class="wizard-step-num"><x-icon :name="$meta['icon']" /></span>
                    <span class="wizard-step-copy">
                        <strong>ขั้นที่ {{ $n }}</strong>
                        <small>{{ $meta['title'] }}</small>
                    </span>
                </div>
            @endforeach
        </nav>

        <section data-step="1" @if($openStep !== 1) hidden @endif>
            <div class="step-banner tone-teal">
                <span class="step-banner-icon"><x-icon name="person" /></span>
                <div>
                    <h2>ขั้นที่ 1 · แนะนำตัว</h2>
                    <p>ชื่อและอีเมลมาจากบัญชีที่ท่านใช้เข้าสู่ระบบแล้ว เมื่อเชื่อมต่อระบบมหาวิทยาลัย สังกัดจะถูกเติมให้โดยอัตโนมัติ</p>
                </div>
            </div>

            <div class="identity-card">
                <span class="identity-avatar" aria-hidden="true"><x-icon name="person" /></span>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <p>{{ auth()->user()->email }}</p>
                </div>
                <span class="identity-pill"><x-icon name="id" /> {{ auth()->user()->account_type->label() }}</span>
            </div>

            <div class="form-block tone-teal">
                <h3 class="form-block-title"><x-icon name="clipboard" /> ข้อมูลติดต่อ</h3>
                <div class="grid-2 step-fields">
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="users" /> ท่านยื่นในฐานะ</span>
                        <select name="requester_kind" required>
                            @foreach (['student_sci' => 'นักศึกษาคณะวิทยาศาสตร์', 'student_other' => 'นักศึกษาต่างคณะ', 'staff_sci' => 'บุคลากรคณะวิทยาศาสตร์', 'staff_kku' => 'บุคลากรหน่วยงานอื่นใน มข.', 'external_person' => 'บุคคลภายนอก', 'external_org' => 'ผู้แทนหน่วยงานภายนอก'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('requester_kind') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="id" /> รหัสนักศึกษาหรือบุคลากร ถ้ามี</span>
                        <input name="identifier" value="{{ old('identifier') }}" placeholder="เช่น รหัสนักศึกษา">
                    </label>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="building" /> สังกัดหรือคณะ</span>
                        <input name="affiliation" value="{{ old('affiliation') }}" placeholder="เช่น คณะวิทยาศาสตร์">
                    </label>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="phone" /> เบอร์ที่ติดต่อได้</span>
                        <input name="phone" value="{{ old('phone') }}" required placeholder="เบอร์โทรที่ติดต่อได้จริง">
                        @error('phone') <span class="error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            <div class="wizard-actions">
                <button class="btn btn-solid" type="button" data-next>ถัดไป เล่าเหตุการณ์ <x-icon name="arrow" /></button>
            </div>
        </section>

        <section data-step="2" @if($openStep !== 2) hidden @endif>
            <div class="step-banner tone-rose">
                <span class="step-banner-icon"><x-icon name="camera" /></span>
                <div>
                    <h2>ขั้นที่ 2 · เกิดอะไรขึ้น</h2>
                    <p>เลือกประเภท ช่วงเวลา และจุดที่จำได้ให้ใกล้เคียงที่สุด เจ้าหน้าที่จะใช้ข้อมูลนี้ตรวจภาพ</p>
                </div>
            </div>

            <div class="form-block tone-rose">
                <h3 class="form-block-title"><x-icon name="flag" /> ประเภทเหตุการณ์</h3>
                <p class="form-block-hint">เลือกประเภทที่ใกล้เคียงที่สุด</p>
                <div class="incident-grid" role="radiogroup" aria-label="ประเภทเหตุการณ์">
                    @foreach ($incidentTypes as $type)
                        @php $tone = $type->tone(); @endphp
                        <label class="incident-card tone-{{ $tone }} {{ old('incident_type') === $type->value ? 'is-on' : '' }}">
                            <input type="radio" name="incident_type" value="{{ $type->value }}" @checked(old('incident_type') === $type->value) required>
                            <span class="incident-icon"><x-icon :name="$type->icon()" /></span>
                            <strong>{{ $type->label() }}</strong>
                            <small>{{ $type->example() }}</small>
                        </label>
                    @endforeach
                </div>
                @error('incident_type') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-block tone-amber">
                <h3 class="form-block-title"><x-icon name="spark" /> ท่านต้องการให้ช่วยเรื่องใด</h3>
                <label class="field field-icon">
                    <span class="field-label"><x-icon name="message" /> สรุปสั้น ๆ</span>
                    <input name="purpose" value="{{ old('purpose') }}" maxlength="500" required placeholder="เช่น ทำกระเป๋าหายใกล้ลิฟต์อาคารเรียน">
                </label>
            </div>

            <div class="form-block tone-blue">
                <h3 class="form-block-title"><x-icon name="calendar" /> ช่วงเวลาโดยประมาณ</h3>
                <div class="grid-2">
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="clock" /> เริ่มประมาณ</span>
                        <input type="datetime-local" name="started_at" value="{{ old('started_at') }}" required>
                    </label>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="clock" /> ถึงประมาณ</span>
                        <input type="datetime-local" name="ended_at" value="{{ old('ended_at') }}" required>
                    </label>
                </div>
            </div>

            <div class="form-block tone-teal place-picker" id="place-picker">
                <h3 class="form-block-title"><x-icon name="building" /> สถานที่เกิดเหตุ</h3>
                <p class="form-block-hint">ค้นหาและเลือกอาคารจากฐานข้อมูล หรือพิมพ์ชื่อใหม่เพื่อเพิ่มเอง ส่วนห้องให้พิมพ์เอง</p>

                <div class="place-section">
                    <span class="place-label"><x-icon name="building" /> ชื่ออาคาร</span>
                    <script type="application/json" id="building-catalog-data">{!! json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) !!}</script>
                    <div
                        class="building-combo"
                        id="building-combo"
                        data-empty="{{ $catalog === [] ? '1' : '0' }}"
                    >
                        <label class="field field-icon building-combo-field" for="building">
                            <span class="field-label"><x-icon name="building" /> ค้นหาหรือเลือกอาคาร</span>
                            <div class="building-combo-shell">
                                <span class="building-combo-ico" aria-hidden="true"><x-icon name="building" /></span>
                                <input
                                    id="building"
                                    class="building-combo-input"
                                    name="building"
                                    type="text"
                                    value="{{ old('building') }}"
                                    required
                                    autocomplete="off"
                                    spellcheck="false"
                                    role="combobox"
                                    aria-expanded="false"
                                    aria-controls="building-listbox"
                                    aria-autocomplete="list"
                                    placeholder="พิมพ์ค้นหา เช่น SC.01 หรือ วิทยวิภาส"
                                >
                                <button class="building-combo-clear" type="button" hidden aria-label="ล้างค่าที่เลือก">×</button>
                                <button class="building-combo-toggle" type="button" aria-label="เปิดรายการอาคาร" tabindex="-1">
                                    <x-icon name="arrow" />
                                </button>
                            </div>
                        </label>
                        <div class="building-combo-panel" id="building-listbox" role="listbox" hidden>
                            <div class="building-combo-meta">
                                <span id="building-combo-count">{{ count($catalog) }} อาคารในระบบ</span>
                                <span>พิมพ์เพื่อค้นหา หรือเพิ่มชื่อใหม่</span>
                            </div>
                            <div class="building-combo-options" id="building-options"></div>
                            <button class="building-combo-create" type="button" id="building-create" hidden>
                                <span class="choice-ico"><x-icon name="spark" /></span>
                                <span>
                                    <strong>เพิ่มอาคารใหม่</strong>
                                    <small id="building-create-label"></small>
                                </span>
                            </button>
                            <p class="building-combo-empty" id="building-empty" hidden>ไม่พบอาคารที่ตรงกัน — กดเพิ่มชื่อใหม่ด้านบนได้</p>
                        </div>
                        <p class="place-hint" id="building-hint">
                            @if ($catalog === [])
                                ยังไม่มีข้อมูลอาคารในตาราง building — พิมพ์ชื่ออาคารแล้วกดเพิ่มเองได้
                            @else
                                เลือกจากรายการ หรือพิมพ์ชื่ออาคารที่ไม่มีในระบบแล้วกด “เพิ่มอาคารใหม่”
                            @endif
                        </p>
                        @error('building') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="place-section">
                    <span class="place-label"><x-icon name="layers" /> ชั้น</span>
                    <div class="choice-grid" id="floor-choices" role="group" aria-label="เลือกชั้น">
                        @foreach (['ชั้นล่าง', '1', '2', '3', '4', '5', '6', '7', '8', 'ดาดฟ้า'] as $floor)
                            <button class="choice choice-zone" type="button" data-floor-value="{{ $floor }}">
                                <x-icon name="layers" />
                                <span>{{ $floor === 'ชั้นล่าง' || $floor === 'ดาดฟ้า' ? $floor : 'ชั้น '.$floor }}</span>
                            </button>
                        @endforeach
                    </div>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="layers" /> ชั้น</span>
                        <input id="floor" name="floor" value="{{ old('floor') }}" autocomplete="off" placeholder="เลือกชั้น หรือพิมพ์เอง เช่น 2">
                    </label>
                </div>

                <div class="place-section">
                    <span class="place-label"><x-icon name="pin" /> ห้องหรือบริเวณที่เกิดเหตุ</span>
                    <div class="choice-grid" id="zone-choices" data-choice-for="location">
                        @foreach ($zones as $zone)
                            <button class="choice choice-zone" type="button" data-value="{{ $zone['label'] }}">
                                <x-icon :name="$zone['icon']" />
                                <span>{{ $zone['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <label class="field field-icon">
                        <span class="field-label"><x-icon name="pin" /> ห้องหรือจุดที่จำได้</span>
                        <input id="location" name="location" value="{{ old('location') }}" required autocomplete="off" placeholder="พิมพ์เอง เช่น 1201 หรือ โถงทางเดิน">
                    </label>
                    <p class="place-hint">ระบุห้องหรือจุดที่จำได้ด้วยตนเอง ไม่ดึงรายการห้องจากฐานข้อมูล</p>
                </div>
            </div>

            <div class="form-block tone-slate">
                <h3 class="form-block-title"><x-icon name="clipboard" /> รายละเอียดเพิ่มเติม</h3>
                <label class="field field-icon">
                    <span class="field-label"><x-icon name="eye" /> จุดสังเกต เช่น สีกระเป๋า หรือหมายเลขรถ</span>
                    <input name="landmark" value="{{ old('landmark') }}" placeholder="สิ่งที่ช่วยให้เจ้าหน้าที่หาภาพได้ง่ายขึ้น">
                </label>

                <div class="urgency-row" role="radiogroup" aria-label="ความเร่งด่วน">
                    <span class="place-label"><x-icon name="flag" /> ความเร่งด่วน</span>
                    <label class="urgency-card tone-green {{ old('urgency', 'normal') === 'normal' ? 'is-on' : '' }}">
                        <input type="radio" name="urgency" value="normal" @checked(old('urgency', 'normal') === 'normal') required>
                        <span class="urgency-icon"><x-icon name="clock" /></span>
                        <span>
                            <strong>รอได้ตามคิว</strong>
                            <small>ดำเนินการตามลำดับปกติ</small>
                        </span>
                    </label>
                    <label class="urgency-card tone-red {{ old('urgency') === 'urgent' ? 'is-on' : '' }}">
                        <input type="radio" name="urgency" value="urgent" @checked(old('urgency') === 'urgent')>
                        <span class="urgency-icon"><x-icon name="alert" /></span>
                        <span>
                            <strong>เร่งด่วน</strong>
                            <small>โปรดดูโดยเร็ว</small>
                        </span>
                    </label>
                </div>

                <label class="field field-icon">
                    <span class="field-label"><x-icon name="users" /> ผู้ประสานงาน ถ้ามี</span>
                    <input name="external_contact_name" value="{{ old('external_contact_name') }}" placeholder="ชื่อผู้ติดต่อเพิ่มเติม">
                </label>
                <label class="field field-icon">
                    <span class="field-label"><x-icon name="message" /> เล่าเหตุการณ์เท่าที่จำได้</span>
                    <textarea name="details" maxlength="2000" required placeholder="เล่าตามลำดับเวลา จุดที่อยู่ และสิ่งที่สังเกตเห็น">{{ old('details') }}</textarea>
                </label>
            </div>

            @if ($onBehalf)
                <div class="form-block tone-indigo">
                    <h3 class="form-block-title"><x-icon name="staff" /> บันทึกแทนผู้แจ้ง</h3>
                    <label class="check"><input type="checkbox" name="on_behalf" value="1" @checked(old('on_behalf'))> <span>บันทึกคำขอแทนผู้แจ้ง</span></label>
                    <label class="field field-icon"><span class="field-label"><x-icon name="route" /> ช่องทางที่รับเรื่อง</span><input name="intake_channel" value="{{ old('intake_channel') }}"></label>
                    <label class="field field-icon"><span class="field-label"><x-icon name="person" /> ชื่อผู้แจ้งตัวจริง</span><input name="informant_name" value="{{ old('informant_name') }}"></label>
                    <label class="field field-icon"><span class="field-label"><x-icon name="message" /> เหตุผลที่บันทึกแทน</span><textarea name="on_behalf_reason">{{ old('on_behalf_reason') }}</textarea></label>
                </div>
            @endif

            <div class="wizard-actions">
                <button class="btn btn-quiet" type="button" data-back><x-icon name="arrow" class="icon create-back-icon" /> ย้อนกลับ</button>
                <button class="btn btn-solid" type="button" data-next>ถัดไป อ่านข้อตกลง <x-icon name="arrow" /></button>
            </div>
        </section>

        <section data-step="3" @if($openStep !== 3) hidden @endif>
            <div class="step-banner tone-indigo">
                <span class="step-banner-icon"><x-icon name="shield" /></span>
                <div>
                    <h2>ขั้นที่ 3 · สิ่งที่ควรรู้ก่อนส่ง</h2>
                    <p>อ่านข้อตกลงสั้น ๆ แล้วยืนยันก่อนส่งคำขอให้เจ้าหน้าที่</p>
                </div>
            </div>

            <div class="privacy-panel">
                <ul class="privacy-list">
                    <li><span class="privacy-ico tone-blue"><x-icon name="eye" /></span> ระบบนี้รับคำขอและนัดดูภาพต่อหน้าเจ้าหน้าที่เท่านั้น</li>
                    <li><span class="privacy-ico tone-rose"><x-icon name="lock" /></span> ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ และไม่มีการบันทึกภาพหน้าจอ</li>
                    <li><span class="privacy-ico tone-teal"><x-icon name="pin" /></span> จะดูเฉพาะจุดและช่วงเวลาที่เกี่ยวข้องกับเรื่องของท่าน</li>
                    <li><span class="privacy-ico tone-amber"><x-icon name="person" /></span> ท่านขอดูหรือแก้ไขข้อมูลของท่านได้โดยติดต่อเจ้าหน้าที่ผู้รับเรื่อง</li>
                </ul>
                <p class="privacy-link"><x-icon name="document" /> <a href="{{ route('privacy.show') }}" target="_blank">อ่านประกาศความเป็นส่วนตัวฉบับเต็ม รุ่น {{ $notice->version }}</a></p>
            </div>
            <input type="hidden" name="privacy_notice_version" value="{{ $notice->version }}">
            <label class="check privacy-accept">
                <input type="checkbox" name="privacy_accepted" value="1" @checked(old('privacy_accepted')) required>
                <span>ข้าพเจ้าได้อ่านและรับทราบประกาศความเป็นส่วนตัวและเงื่อนไขการเข้าตรวจดูภาพภายใต้การควบคุมของเจ้าหน้าที่แล้ว</span>
            </label>
            @error('privacy_accepted') <p class="error">กรุณารับทราบประกาศความเป็นส่วนตัวก่อนส่งคำขอ</p> @enderror
            <div class="wizard-actions">
                <button class="btn btn-quiet" type="button" data-back><x-icon name="arrow" class="icon create-back-icon" /> ย้อนกลับ</button>
                <button class="btn btn-solid" type="submit"><x-icon name="check" /> ส่งคำขอ</button>
            </div>
        </section>
    </form>
    @push('styles')
    <style>
        .create-form { overflow: hidden; }
        .create-back {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 0.85rem;
        }
        .create-back-icon { transform: scaleX(-1); width: 1.05rem; height: 1.05rem; }
        .create-hero {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .create-hero-icon {
            width: 3.2rem;
            height: 3.2rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            flex: none;
            background: linear-gradient(145deg, #e11d48, #9f1239);
            color: #fff;
            box-shadow: 0 10px 22px rgba(190, 18, 60, 0.22);
        }
        .create-hero-icon .icon { width: 1.55rem; height: 1.55rem; }
        .create-hero h1 { margin: 0 0 0.35rem; font-size: 1.55rem; }
        .create-hero .lead { margin: 0; color: var(--muted); }

        .wizard-track {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.55rem;
            margin: 0 0 1.25rem;
        }
        .wizard-step {
            display: flex;
            gap: 0.55rem;
            align-items: center;
            padding: 0.7rem 0.75rem;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--muted);
            min-width: 0;
        }
        .wizard-step-num {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 999px;
            display: grid;
            place-items: center;
            flex: none;
            background: #f8efec;
            color: var(--muted);
        }
        .wizard-step-num .icon { width: 1.05rem; height: 1.05rem; }
        .wizard-step-copy { min-width: 0; display: grid; gap: 0.05rem; }
        .wizard-step-copy strong { font-size: 0.82rem; line-height: 1.2; }
        .wizard-step-copy small { font-size: 0.9rem; color: var(--ink); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wizard-step.is-current {
            border-color: #fda4af;
            background: linear-gradient(180deg, #fff1f2 0%, #ffffff 70%);
            color: var(--help);
            box-shadow: 0 8px 18px rgba(190, 18, 60, 0.08);
        }
        .wizard-step.is-current .wizard-step-num { background: #e11d48; color: #fff; }
        .wizard-step.is-done {
            border-color: #a7f3d0;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 70%);
            color: var(--success);
        }
        .wizard-step.is-done .wizard-step-num { background: #059669; color: #fff; }

        .step-banner {
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
            padding: 1rem 1.05rem;
            border-radius: 14px;
            border: 1px solid transparent;
            margin-bottom: 1rem;
        }
        .step-banner h2 { margin: 0 0 0.25rem; font-size: 1.18rem; }
        .step-banner p { margin: 0; color: var(--muted); }
        .step-banner-icon {
            width: 2.55rem;
            height: 2.55rem;
            border-radius: 0.85rem;
            display: grid;
            place-items: center;
            flex: none;
            color: #fff;
        }
        .step-banner.tone-teal { background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 75%); border-color: #99f6e4; }
        .step-banner.tone-teal .step-banner-icon { background: #0d9488; }
        .step-banner.tone-rose { background: linear-gradient(180deg, #fff1f2 0%, #ffffff 75%); border-color: #fecdd3; }
        .step-banner.tone-rose .step-banner-icon { background: #e11d48; }
        .step-banner.tone-indigo { background: linear-gradient(180deg, #eef2ff 0%, #ffffff 75%); border-color: #c7d2fe; }
        .step-banner.tone-indigo .step-banner-icon { background: #4f46e5; }

        .identity-card {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            align-items: center;
            padding: 1rem 1.05rem;
            border-radius: 14px;
            border: 1px solid #99f6e4;
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 55%);
            margin-bottom: 1rem;
        }
        .identity-avatar {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #0d9488;
            color: #fff;
            flex: none;
        }
        .identity-card strong { display: block; font-size: 1.05rem; }
        .identity-card p { margin: 0.1rem 0 0; color: var(--muted); }
        .identity-pill {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #ccfbf1;
            color: #0f766e;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .identity-pill .icon { width: 1rem; height: 1rem; }

        .form-block {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1rem 1.05rem 0.85rem;
            margin-bottom: 0.95rem;
            background: #fff;
        }
        .form-block.tone-teal { border-color: #99f6e4; background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 42%); }
        .form-block.tone-rose { border-color: #fecdd3; background: linear-gradient(180deg, #fff1f2 0%, #ffffff 38%); }
        .form-block.tone-amber { border-color: #fcd34d; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 42%); }
        .form-block.tone-blue { border-color: #bfdbfe; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 42%); }
        .form-block.tone-slate { border-color: #cbd5e1; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 42%); }
        .form-block.tone-indigo { border-color: #c7d2fe; background: linear-gradient(180deg, #eef2ff 0%, #ffffff 42%); }
        .form-block-title {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin: 0 0 0.55rem;
            font-size: 1.02rem;
        }
        .form-block-title .icon { width: 1.15rem; height: 1.15rem; color: var(--help); }
        .form-block.tone-teal .form-block-title .icon { color: #0f766e; }
        .form-block.tone-rose .form-block-title .icon { color: #be123c; }
        .form-block.tone-amber .form-block-title .icon { color: #b45309; }
        .form-block.tone-blue .form-block-title .icon { color: #1d4ed8; }
        .form-block.tone-slate .form-block-title .icon { color: #475569; }
        .form-block.tone-indigo .form-block-title .icon { color: #4338ca; }
        .form-block-hint { margin: -0.15rem 0 0.75rem; color: var(--muted); }

        .field-icon .field-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .field-icon .field-label .icon {
            width: 1rem;
            height: 1rem;
            color: var(--help);
            opacity: 0.9;
        }
        .field-icon input:focus,
        .field-icon select:focus,
        .field-icon textarea:focus {
            border-color: #fda4af;
            box-shadow: 0 0 0 3px rgba(253, 164, 175, 0.28);
        }

        .incident-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }
        .incident-card {
            display: grid;
            gap: 0.25rem;
            padding: 0.85rem 0.9rem;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            cursor: pointer;
            transition: border-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
        }
        .incident-card input { position: absolute; opacity: 0; pointer-events: none; }
        .incident-card:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(28, 25, 23, 0.06); }
        .incident-icon {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.7rem;
            display: grid;
            place-items: center;
            color: #fff;
            margin-bottom: 0.2rem;
        }
        .incident-card strong { font-size: 0.98rem; }
        .incident-card small { color: var(--muted); line-height: 1.4; }
        .incident-card.tone-amber .incident-icon { background: #d97706; }
        .incident-card.tone-rose .incident-icon { background: #e11d48; }
        .incident-card.tone-blue .incident-icon { background: #2563eb; }
        .incident-card.tone-teal .incident-icon { background: #0d9488; }
        .incident-card.tone-red .incident-icon { background: #be123c; }
        .incident-card.tone-slate .incident-icon { background: #475569; }
        .incident-card.tone-amber { border-color: #fcd34d; background: #fffbeb; }
        .incident-card.tone-rose { border-color: #fecdd3; background: #fff1f2; }
        .incident-card.tone-blue { border-color: #bfdbfe; background: #eff6ff; }
        .incident-card.tone-teal { border-color: #99f6e4; background: #f0fdfa; }
        .incident-card.tone-red { border-color: #fda4af; background: #fff1f2; }
        .incident-card.tone-slate { border-color: #cbd5e1; background: #f8fafc; }
        .incident-card.is-on {
            box-shadow: 0 0 0 2px currentColor inset, 0 8px 18px rgba(28, 25, 23, 0.06);
            font-weight: 600;
        }
        .incident-card.tone-amber.is-on { color: #b45309; }
        .incident-card.tone-rose.is-on,
        .incident-card.tone-red.is-on { color: #be123c; }
        .incident-card.tone-blue.is-on { color: #1d4ed8; }
        .incident-card.tone-teal.is-on { color: #0f766e; }
        .incident-card.tone-slate.is-on { color: #334155; }

        .place-section { margin-bottom: 0.9rem; }
        .place-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            color: #0f766e;
        }
        .place-label .icon { width: 1.05rem; height: 1.05rem; }
        .choice-ico {
            width: 1.7rem;
            height: 1.7rem;
            border-radius: 0.55rem;
            display: grid;
            place-items: center;
            background: #ccfbf1;
            color: #0f766e;
            flex: none;
        }
        .choice-ico .icon { width: 1rem; height: 1rem; }
        .choice-zone {
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            gap: 0.4rem;
            background: #f0fdfa;
            border-color: #99f6e4;
            color: #0f766e;
        }
        .choice-zone .icon { width: 1.05rem; height: 1.05rem; }
        .choice-zone.is-on { background: #0d9488; border-color: #0d9488; color: #fff; }
        .place-hint { color: var(--muted); margin: 0.35rem 0 0.75rem; }

        .building-combo { position: relative; }
        .building-combo-field { margin-bottom: 0; }
        .building-combo-shell {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #99f6e4;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 100%);
            padding: 0.2rem 0.35rem 0.2rem 0.55rem;
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.08);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .building-combo.is-open .building-combo-shell,
        .building-combo-shell:focus-within {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.28), 0 10px 24px rgba(13, 148, 136, 0.12);
        }
        .building-combo.has-value .building-combo-shell {
            border-color: #14b8a6;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 70%);
        }
        .building-combo-ico {
            width: 2rem;
            height: 2rem;
            border-radius: 0.7rem;
            display: grid;
            place-items: center;
            background: #0d9488;
            color: #fff;
        }
        .building-combo-ico .icon { width: 1.05rem; height: 1.05rem; }
        .building-combo-input {
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0.75rem 0.25rem !important;
            min-width: 0;
            font-weight: 600;
        }
        .building-combo-toggle,
        .building-combo-clear {
            width: 2.15rem;
            height: 2.15rem;
            border: 0;
            border-radius: 0.7rem;
            background: #ccfbf1;
            color: #0f766e;
            display: grid;
            place-items: center;
            cursor: pointer;
            font: inherit;
        }
        .building-combo-toggle .icon {
            width: 1rem;
            height: 1rem;
            transform: rotate(90deg);
            transition: transform 0.15s ease;
        }
        .building-combo.is-open .building-combo-toggle .icon { transform: rotate(-90deg); }
        .building-combo-clear { font-size: 1.25rem; line-height: 1; font-weight: 700; }
        .building-combo-panel {
            position: absolute;
            z-index: 30;
            left: 0;
            right: 0;
            top: calc(100% - 0.35rem);
            margin-top: 0.35rem;
            border: 1px solid #99f6e4;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 118, 110, 0.16);
            overflow: hidden;
        }
        .building-combo-meta {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding: 0.7rem 0.95rem;
            background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
            border-bottom: 1px solid #ccfbf1;
            color: var(--muted);
            font-size: 0.88rem;
        }
        .building-combo-meta span:first-child { color: #0f766e; font-weight: 700; }
        .building-combo-options {
            max-height: 16rem;
            overflow: auto;
            padding: 0.35rem;
        }
        .building-option {
            width: 100%;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.7rem;
            align-items: center;
            text-align: left;
            border: 0;
            border-radius: 12px;
            background: transparent;
            padding: 0.7rem 0.75rem;
            cursor: pointer;
            font: inherit;
            color: inherit;
        }
        .building-option:hover,
        .building-option.is-active {
            background: #f0fdfa;
        }
        .building-option.is-selected {
            background: #ccfbf1;
            box-shadow: inset 0 0 0 1px #5eead4;
        }
        .building-option strong { display: block; font-size: 0.98rem; }
        .building-option small { color: var(--muted); }
        .building-option-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 3.4rem;
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: #ecfdf5;
            color: #0f766e;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1px solid #99f6e4;
        }
        .building-combo-create {
            width: calc(100% - 0.7rem);
            margin: 0.2rem 0.35rem 0.45rem;
            display: flex;
            gap: 0.7rem;
            align-items: center;
            text-align: left;
            border: 1px dashed #5eead4;
            border-radius: 12px;
            background: #f0fdfa;
            padding: 0.75rem 0.85rem;
            cursor: pointer;
            font: inherit;
            color: #0f766e;
        }
        .building-combo-create strong { display: block; }
        .building-combo-create small { color: var(--muted); }
        .building-combo-create.is-active,
        .building-combo-create:hover {
            background: #ccfbf1;
            border-style: solid;
        }
        .building-combo-empty {
            margin: 0;
            padding: 0.85rem 1rem 1rem;
            color: var(--muted);
        }

        .urgency-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
            margin: 0.35rem 0 0.9rem;
        }
        .urgency-row > .place-label { grid-column: 1 / -1; color: #475569; }
        .urgency-card {
            display: flex;
            gap: 0.7rem;
            align-items: center;
            padding: 0.85rem 0.9rem;
            border-radius: 12px;
            border: 1px solid var(--line);
            cursor: pointer;
            background: #fff;
            position: relative;
        }
        .urgency-card input { position: absolute; opacity: 0; pointer-events: none; }
        .urgency-icon {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.7rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
        }
        .urgency-card strong { display: block; }
        .urgency-card small { color: var(--muted); }
        .urgency-card.tone-green { border-color: #a7f3d0; background: #ecfdf5; }
        .urgency-card.tone-green .urgency-icon { background: #059669; }
        .urgency-card.tone-red { border-color: #fecdd3; background: #fff1f2; }
        .urgency-card.tone-red .urgency-icon { background: #e11d48; }
        .urgency-card.is-on { box-shadow: 0 0 0 2px currentColor inset; }
        .urgency-card.tone-green.is-on { color: #047857; }
        .urgency-card.tone-red.is-on { color: #be123c; }

        .privacy-panel {
            border: 1px solid #c7d2fe;
            border-radius: 14px;
            padding: 1rem 1.05rem;
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 55%);
            margin-bottom: 0.85rem;
        }
        .privacy-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.7rem;
        }
        .privacy-list li {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
        }
        .privacy-ico {
            width: 2rem;
            height: 2rem;
            border-radius: 0.65rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
        }
        .privacy-ico .icon { width: 1.05rem; height: 1.05rem; }
        .privacy-ico.tone-blue { background: #2563eb; }
        .privacy-ico.tone-rose { background: #e11d48; }
        .privacy-ico.tone-teal { background: #0d9488; }
        .privacy-ico.tone-amber { background: #d97706; }
        .privacy-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0.95rem 0 0;
            font-weight: 600;
        }
        .privacy-link a { color: #4338ca; }
        .privacy-accept {
            margin: 0.35rem 0 0;
            padding: 0.95rem 1rem;
            border: 1px solid var(--accent);
            border-radius: 12px;
            background: var(--info-bg);
        }

        .step-fields { margin-bottom: 0.25rem; }
        .step-fields .field { margin-bottom: 0; min-width: 0; }
        .wizard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            margin-top: 1.75rem;
            padding-top: 0.35rem;
            clear: both;
            position: relative;
            z-index: 0;
        }

        @media (max-width: 720px) {
            .wizard-track { grid-template-columns: 1fr; }
            .incident-grid,
            .urgency-row { grid-template-columns: 1fr; }
            .identity-pill { margin-left: 0; }
            .create-hero { gap: 0.75rem; }
            .create-hero h1 { font-size: 1.35rem; }
        }
    </style>
    @endpush
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

        const syncChoiceCards = (selector) => {
            document.querySelectorAll(selector).forEach((card) => {
                const input = card.querySelector('input');
                const refresh = () => {
                    document.querySelectorAll(selector).forEach((item) => {
                        item.classList.toggle('is-on', item.querySelector('input')?.checked);
                    });
                };
                card.addEventListener('click', () => {
                    if (input) input.checked = true;
                    refresh();
                });
                input?.addEventListener('change', refresh);
            });
        };
        syncChoiceCards('.incident-card');
        syncChoiceCards('.urgency-card');

        const floorInput = document.getElementById('floor');
        const locationInput = document.getElementById('location');

        const markActive = (group, active) => {
            group?.querySelectorAll('.choice').forEach((item) => item.classList.toggle('is-on', item === active));
        };

        document.getElementById('floor-choices')?.querySelectorAll('[data-floor-value]').forEach((choice) => {
            choice.addEventListener('click', () => {
                markActive(choice.parentElement, choice);
                floorInput.value = choice.dataset.floorValue || '';
                floorInput.focus();
            });
        });

        document.getElementById('zone-choices')?.querySelectorAll('[data-value]').forEach((choice) => {
            choice.addEventListener('click', () => {
                markActive(choice.parentElement, choice);
                locationInput.value = choice.dataset.value || '';
                locationInput.focus();
            });
        });

        if (floorInput?.value) {
            const match = [...document.querySelectorAll('#floor-choices [data-floor-value]')].find((choice) => (
                choice.dataset.floorValue === floorInput.value
            ));
            if (match) markActive(match.parentElement, match);
        }

        (() => {
            const root = document.getElementById('building-combo');
            if (!root) return;

            const buildings = (() => {
                try {
                    const raw = document.getElementById('building-catalog-data')?.textContent || '[]';
                    return JSON.parse(raw);
                } catch {
                    return [];
                }
            })();
            const input = document.getElementById('building');
            const panel = document.getElementById('building-listbox');
            const options = document.getElementById('building-options');
            const createBtn = document.getElementById('building-create');
            const createLabel = document.getElementById('building-create-label');
            const empty = document.getElementById('building-empty');
            const count = document.getElementById('building-combo-count');
            const clearBtn = root.querySelector('.building-combo-clear');
            const toggleBtn = root.querySelector('.building-combo-toggle');
            let activeIndex = -1;
            let open = false;
            let confirmed = (input.value || '').trim() !== '';

            const normalize = (value) => (value || '').toString().trim().toLowerCase();
            const currentValue = () => (input.value || '').trim();

            const setOpen = (next) => {
                open = next;
                panel.hidden = !next;
                root.classList.toggle('is-open', next);
                input.setAttribute('aria-expanded', next ? 'true' : 'false');
                if (next) render();
            };

            const syncChrome = () => {
                const value = currentValue();
                root.classList.toggle('has-value', confirmed && value !== '');
                clearBtn.hidden = value === '';
            };

            const setValue = (value, { close = true, confirm = true } = {}) => {
                const next = (value || '').trim();
                input.value = next;
                confirmed = confirm && next !== '';
                syncChrome();
                if (close) setOpen(false);
                render();
            };

            const filtered = () => {
                const q = normalize(input.value);
                if (!q) return buildings;
                return buildings.filter((item) => (
                    normalize(item.label).includes(q) || normalize(item.code).includes(q)
                ));
            };

            const exactMatch = () => {
                const q = normalize(input.value);
                if (!q) return null;
                return buildings.find((item) => normalize(item.label) === q || normalize(item.code) === q) || null;
            };

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;');

            const render = () => {
                const items = filtered();
                const query = currentValue();
                const selected = confirmed ? query : '';
                const match = exactMatch();

                count.textContent = items.length === buildings.length
                    ? `${buildings.length} อาคารในระบบ`
                    : `พบ ${items.length} จาก ${buildings.length} อาคาร`;

                options.innerHTML = items.map((item, index) => {
                    const isSelected = selected === item.label || selected === item.code;
                    const rooms = item.rooms ? `${item.rooms} จุด/ห้อง` : item.code;
                    return `
                        <button class="building-option${isSelected ? ' is-selected' : ''}${index === activeIndex ? ' is-active' : ''}" type="button" role="option" data-index="${index}" data-label="${escapeHtml(item.label)}" aria-selected="${isSelected ? 'true' : 'false'}">
                            <span class="choice-ico"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
                            <span><strong>${escapeHtml(item.label)}</strong><small>${escapeHtml(item.code)}</small></span>
                            <span class="building-option-badge">${escapeHtml(rooms)}</span>
                        </button>
                    `;
                }).join('');

                const showCreate = query !== '' && !match;
                createBtn.hidden = !showCreate;
                createLabel.textContent = query ? `“${query}”` : '';
                empty.hidden = !(items.length === 0 && !showCreate);

                options.querySelectorAll('.building-option').forEach((button) => {
                    button.addEventListener('click', () => setValue(button.dataset.label));
                });
            };

            const moveActive = (delta) => {
                const buttons = [...options.querySelectorAll('.building-option')];
                const createVisible = !createBtn.hidden;
                const total = buttons.length + (createVisible ? 1 : 0);
                if (total === 0) return;
                activeIndex = (activeIndex + delta + total) % total;
                buttons.forEach((button, index) => button.classList.toggle('is-active', index === activeIndex));
                createBtn.classList.toggle('is-active', createVisible && activeIndex === buttons.length);
                const current = activeIndex < buttons.length ? buttons[activeIndex] : createBtn;
                current?.scrollIntoView({ block: 'nearest' });
            };

            input.addEventListener('focus', () => setOpen(true));
            input.addEventListener('input', () => {
                confirmed = false;
                syncChrome();
                activeIndex = -1;
                setOpen(true);
                render();
            });
            input.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (!open) setOpen(true);
                    moveActive(1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (!open) setOpen(true);
                    moveActive(-1);
                } else if (event.key === 'Enter') {
                    const buttons = [...options.querySelectorAll('.building-option')];
                    if (open && activeIndex >= 0 && activeIndex < buttons.length) {
                        event.preventDefault();
                        setValue(buttons[activeIndex].dataset.label);
                    } else if (open && !createBtn.hidden && activeIndex === buttons.length) {
                        event.preventDefault();
                        setValue(input.value);
                    } else if (currentValue() !== '') {
                        event.preventDefault();
                        const match = exactMatch();
                        setValue(match ? match.label : input.value);
                    }
                } else if (event.key === 'Escape') {
                    setOpen(false);
                }
            });
            input.addEventListener('blur', () => {
                // Keep typed custom names usable even if the user does not click "เพิ่ม".
                if (currentValue() !== '') confirmed = true;
                syncChrome();
            });

            createBtn.addEventListener('click', () => setValue(input.value));
            toggleBtn.addEventListener('click', () => {
                setOpen(!open);
                input.focus();
            });
            clearBtn.addEventListener('click', () => {
                setValue('');
                input.focus();
                setOpen(true);
            });

            document.addEventListener('click', (event) => {
                if (!root.contains(event.target)) setOpen(false);
            });

            if (currentValue()) {
                setValue(currentValue(), { close: false });
            } else {
                syncChrome();
                render();
            }
        })();
    </script>
@endsection
