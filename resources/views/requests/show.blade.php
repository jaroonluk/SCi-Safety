@extends('layouts.app')

@section('title', $viewingRequest->reference.' | SCi-Safety')

@section('content')
    @php
        $journey = $viewingRequest->journey();
        $reviewer = in_array(auth()->user()->role, [\App\Enums\UserRole::Director, \App\Enums\UserRole::AssociateDean], true);
        $appointmentLocal = \App\Support\ThaiDate::at($viewingRequest->appointment_at);
        $calendarStart = $appointmentLocal?->copy()->utc()->format('Ymd\THis\Z');
        $calendarEnd = $appointmentLocal?->copy()->addHour()->utc()->format('Ymd\THis\Z');
        $calendarTitle = rawurlencode('นัดดูภาพ '.$viewingRequest->reference);
        $calendarPlace = rawurlencode($viewingRequest->appointment_place ?? 'ห้องควบคุมความปลอดภัย คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น');
        $isOfficer = auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin);
        $isOwner = $viewingRequest->user_id === auth()->id();
        $hasAppointment = filled($viewingRequest->appointment_at);
        $needsConfirm = $isOwner
            && $viewingRequest->status === \App\Enums\RequestStatus::Scheduled
            && ! $viewingRequest->appointment_confirmed_at;
        $floorLabel = filled($viewingRequest->floor)
            ? (preg_match('/^\d+$/u', (string) $viewingRequest->floor) ? 'ชั้น '.$viewingRequest->floor : $viewingRequest->floor)
            : null;
        $placeParts = array_values(array_filter([
            $viewingRequest->building,
            $floorLabel,
            $viewingRequest->location,
        ]));
    @endphp
    <article class="card page request-show">
        <a class="back request-back" href="{{ route('requests.index') }}"><x-icon name="arrow" class="icon request-back-icon" /> กลับคำขอของฉัน</a>

        <header class="request-hero">
            <div>
                <p class="request-kicker">รายละเอียดคำขอ</p>
                <h1>{{ $viewingRequest->reference }}</h1>
                @if ($isOwner && $hasAppointment)
                    <p class="request-next">มีนัดดูภาพแล้ว — ดูวันเวลาและสถานที่ด้านล่างนี้ก่อน</p>
                @else
                    <p class="request-next">ติดตามได้ว่าอยู่ขั้นตอนไหน จากทั้งหมด {{ $journey['total'] }} ขั้นตอน</p>
                @endif
            </div>
            <span class="pill pill-{{ $viewingRequest->status->tone() }}">{{ $viewingRequest->statusLabel() }}</span>
        </header>

        @if ($isOwner && $hasAppointment)
            <section class="appointment-spotlight" aria-label="นัดดูภาพ">
                <p class="appointment-spotlight-kicker"><x-icon name="calendar" /> นัดดูภาพของคุณ</p>
                <h2 class="appointment-spotlight-when">{{ \App\Support\ThaiDate::datetime($viewingRequest->appointment_at, true) }}</h2>
                <dl class="appointment-spotlight-facts">
                    <div>
                        <dt>สถานที่นัด</dt>
                        <dd>{{ $viewingRequest->appointment_place }}</dd>
                    </div>
                    <div>
                        <dt>ผู้ร่วม</dt>
                        <dd>{{ $viewingRequest->participants }}</dd>
                    </div>
                    @if ($viewingRequest->appointment_confirmed_at)
                        <div>
                            <dt>สถานะการนัด</dt>
                            <dd>ยืนยันแล้วว่ามาตามนัดได้</dd>
                        </div>
                    @endif
                </dl>
                <div class="appointment-spotlight-actions">
                    @if ($needsConfirm)
                        <form method="POST" action="{{ route('requests.confirm', $viewingRequest) }}">
                            @csrf
                            <button class="btn btn-solid" type="submit">ยืนยันว่ามาตามนัดได้</button>
                        </form>
                    @endif
                    <a class="btn btn-quiet" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ $calendarTitle }}&dates={{ $calendarStart }}/{{ $calendarEnd }}&location={{ $calendarPlace }}">เพิ่มใน Google Calendar</a>
                </div>
            </section>
        @endif

        @include('requests.partials.progress')

        @if ($reviewer)
            <section class="detail-block tone-sand">
                <div class="detail-block-head">
                    <span class="detail-block-icon"><x-icon name="scale" /></span>
                    <div>
                        <h2>สรุปสำหรับผู้พิจารณา</h2>
                        <p>อ่านสั้น ๆ ก่อนลงความเห็น</p>
                    </div>
                </div>
                <div class="detail-facts">
                    <div><span>ประเภท</span><strong>{{ $viewingRequest->incident_type->label() }}</strong></div>
                    <div><span>ช่วงเวลา</span><strong>{{ \App\Support\ThaiDate::range($viewingRequest->started_at, $viewingRequest->ended_at) }}</strong></div>
                    <div class="span-2"><span>สถานที่</span><strong>{{ implode(' · ', $placeParts) }}</strong></div>
                    <div class="span-2"><span>ผู้ยื่นต้องการ</span><strong>{{ $viewingRequest->purpose }}</strong></div>
                    <div class="span-2"><span>รายละเอียด</span><strong class="detail-prose">{{ $viewingRequest->details }}</strong></div>
                </div>
            </section>

            <div class="review-decision-zone">
                @include('requests.partials.review-trail', ['compact' => false])

                @if (auth()->user()->role === \App\Enums\UserRole::Director && $viewingRequest->status === \App\Enums\RequestStatus::PendingDirector)
                    @include('requests.partials.decision', ['allowEscalate' => true])
                @endif
                @if (auth()->user()->role === \App\Enums\UserRole::AssociateDean && $viewingRequest->status === \App\Enums\RequestStatus::PendingDean)
                    @include('requests.partials.decision', ['allowEscalate' => false])
                @endif
            </div>
        @elseif ($viewingRequest->technical_note || $viewingRequest->director_note || $viewingRequest->dean_note || $viewingRequest->technical_result)
            @include('requests.partials.review-trail')
        @endif

        @if ($hasAppointment && ! $isOwner)
            <section class="detail-block tone-blue">
                <div class="detail-block-head">
                    <span class="detail-block-icon"><x-icon name="calendar" /></span>
                    <div>
                        <h2>นัดดูภาพ</h2>
                        <p>วันเวลาตามเวลามาตรฐานประเทศไทย</p>
                    </div>
                </div>
                <div class="detail-facts">
                    <div class="span-2"><span>วันและเวลา</span><strong>{{ \App\Support\ThaiDate::datetime($viewingRequest->appointment_at, true) }}</strong></div>
                    <div><span>สถานที่นัด</span><strong>{{ $viewingRequest->appointment_place }}</strong></div>
                    <div><span>ผู้ร่วม</span><strong>{{ $viewingRequest->participants }}</strong></div>
                </div>
                <div class="detail-actions">
                    <a class="btn btn-quiet" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ $calendarTitle }}&dates={{ $calendarStart }}/{{ $calendarEnd }}&location={{ $calendarPlace }}">เพิ่มใน Google Calendar</a>
                </div>
            </section>
        @endif

        @unless ($reviewer)
            <section class="detail-block tone-rose">
                <div class="detail-block-head">
                    <span class="detail-block-icon"><x-icon name="clipboard" /></span>
                    <div>
                        <h2>ข้อมูลที่ท่านกรอก</h2>
                        <p>สรุปคำขอที่ส่งแล้ว อ่านตามหมวดได้สะดวก</p>
                    </div>
                </div>

                <div class="detail-sections">
                    <div class="detail-section">
                        <h3><x-icon name="person" /> ผู้ยื่นคำขอ</h3>
                        <div class="detail-facts">
                            <div><span>ชื่อ</span><strong>{{ $viewingRequest->user->name }}</strong></div>
                            <div><span>ยื่นในฐานะ</span><strong>{{ $viewingRequest->requesterKindLabel() }}</strong></div>
                            <div><span>เบอร์ติดต่อ</span><strong>{{ $viewingRequest->phone }}</strong></div>
                            @if ($viewingRequest->affiliation)
                                <div><span>สังกัด</span><strong>{{ $viewingRequest->affiliation }}</strong></div>
                            @endif
                            @if ($viewingRequest->identifier)
                                <div><span>รหัส</span><strong>{{ $viewingRequest->identifier }}</strong></div>
                            @endif
                            @if ($viewingRequest->external_contact_name)
                                <div><span>ผู้ประสานงาน</span><strong>{{ $viewingRequest->external_contact_name }}</strong></div>
                            @endif
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3><x-icon name="flag" /> เหตุการณ์</h3>
                        <div class="detail-facts">
                            <div><span>ประเภท</span><strong>{{ $viewingRequest->incident_type->label() }}</strong></div>
                            <div>
                                <span>ความเร่งด่วน</span>
                                <strong>
                                    <span class="urgency-pill {{ $viewingRequest->urgency === 'urgent' ? 'is-urgent' : 'is-normal' }}">
                                        {{ $viewingRequest->urgencyLabel() }}
                                    </span>
                                </strong>
                            </div>
                            <div class="span-2"><span>ต้องการให้ช่วยเรื่อง</span><strong>{{ $viewingRequest->purpose }}</strong></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3><x-icon name="clock" /> ช่วงเวลาที่ขอตรวจ</h3>
                        <div class="detail-time">
                            <div>
                                <span>เริ่มประมาณ</span>
                                <strong>{{ \App\Support\ThaiDate::datetime($viewingRequest->started_at, true) }}</strong>
                            </div>
                            <div class="detail-time-divider" aria-hidden="true">ถึง</div>
                            <div>
                                <span>ถึงประมาณ</span>
                                <strong>{{ \App\Support\ThaiDate::datetime($viewingRequest->ended_at, true) }}</strong>
                            </div>
                        </div>
                        <p class="detail-time-summary">รวมช่วงเวลา · {{ \App\Support\ThaiDate::range($viewingRequest->started_at, $viewingRequest->ended_at) }}</p>
                    </div>

                    <div class="detail-section">
                        <h3><x-icon name="building" /> สถานที่เกิดเหตุ</h3>
                        <div class="detail-facts">
                            <div><span>อาคาร</span><strong>{{ $viewingRequest->building }}</strong></div>
                            <div><span>ชั้น</span><strong>{{ $floorLabel ?: 'ไม่ระบุ' }}</strong></div>
                            <div class="span-2"><span>ห้องหรือบริเวณ</span><strong>{{ $viewingRequest->location }}</strong></div>
                            @if ($viewingRequest->landmark)
                                <div class="span-2"><span>จุดสังเกต</span><strong>{{ $viewingRequest->landmark }}</strong></div>
                            @endif
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3><x-icon name="message" /> รายละเอียดเหตุการณ์</h3>
                        <p class="detail-prose">{{ $viewingRequest->details }}</p>
                    </div>
                </div>
            </section>
        @endunless

        <p class="request-footnote"><x-icon name="lock" /> การอนุมัติหมายถึงให้นัดดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</p>

        @if ($viewingRequest->user_id === auth()->id() && $viewingRequest->status === \App\Enums\RequestStatus::MoreInfo)
            <form method="POST" action="{{ route('requests.supplement', $viewingRequest) }}">
                @csrf
                <h2>ข้อมูลที่ขอเพิ่ม</h2>
                <p>พิมพ์รายละเอียดหรือเลขที่เอกสาร เช่น ใบแจ้งความ ได้ที่นี่ โดยไม่ต้องแนบภาพหรือวิดีโอ</p>
                <label class="field">ข้อความถึงเจ้าหน้าที่<textarea name="supplement_note" required>{{ old('supplement_note') }}</textarea></label>
                <button class="btn btn-solid" type="submit">ส่งข้อมูลเพิ่ม</button>
            </form>
        @endif

        @if ($isOfficer && $viewingRequest->status === \App\Enums\RequestStatus::Submitted)
            <form id="camera" method="POST" action="{{ route('requests.technical', $viewingRequest) }}" class="decision-dock action-panel tone-check">
                @csrf
                <input type="hidden" name="decision" id="officer-decision" value="">
                <div class="action-panel-head">
                    <span class="action-panel-icon"><x-icon name="camera" /></span>
                    <div>
                        <h2>ตรวจสอบคำขอและแจ้งผู้บริหาร</h2>
                        <p>บันทึกว่ามีภาพหรือไม่ จากนั้นเห็นชอบหรือไม่เห็นชอบคำขอ หากเห็นชอบ ระบบจะส่งให้ผู้อำนวยการกองพิจารณา</p>
                    </div>
                </div>
                <label class="field">ผลตรวจกล้อง
                    <select name="technical_result" required>
                        @foreach ($technicalResults as $result)
                            <option value="{{ $result->value }}" @selected(old('technical_result') === $result->value)>{{ $result->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">ข้อความแจ้งผู้บริหาร (มีภาพ / ไม่มีภาพ / เหตุผลอื่น)
                    <textarea name="technical_note" required placeholder="เช่น พบภาพชัดเจนในช่วงเวลาที่แจ้ง / ไม่พบภาพเพราะจุดอับ / ภาพไม่ชัดแต่ยังใช้ประกอบได้">{{ old('technical_note') }}</textarea>
                </label>
                <label class="field">หากไม่มีภาพ ให้ระบุความสำคัญของจุดนี้
                    <select name="gap_priority">
                        <option value="medium">ปานกลาง</option>
                        <option value="high">สูง</option>
                        <option value="low">ต่ำ</option>
                    </select>
                </label>
                <div class="decision-bar" style="margin-top:0.85rem">
                    <button class="btn btn-success" type="button" data-officer-decision="approve"><x-icon name="check" /> เห็นชอบคำขอ</button>
                    <button class="btn btn-danger" type="button" data-officer-decision="reject"><x-icon name="alert" /> ไม่เห็นชอบคำขอ</button>
                </div>
                <div id="officer-reject-sheet" class="sheet" hidden>
                    <label class="field">เหตุผลที่ไม่เห็นชอบ
                        <textarea name="reject_reason" placeholder="อธิบายให้ผู้ยื่นเข้าใจว่าเหตุใดยังไม่เห็นชอบ">{{ old('reject_reason') }}</textarea>
                    </label>
                </div>
                <button class="btn btn-solid" type="submit" id="officer-submit" hidden>ยืนยันการบันทึก</button>
            </form>
            <form method="POST" action="{{ route('requests.more', $viewingRequest) }}" class="action-panel tone-wait" style="margin-top:1rem">
                @csrf
                <div class="action-panel-head">
                    <span class="action-panel-icon"><x-icon name="message" /></span>
                    <div>
                        <h2>ขอข้อมูลเพิ่มจากผู้ยื่น</h2>
                        <p>ใช้เมื่อรายละเอียดยังไม่พอให้ตรวจกล้องได้ชัดเจน</p>
                    </div>
                </div>
                <label class="field">ข้อความถึงผู้ยื่น<textarea name="more_info_note" required>{{ old('more_info_note') }}</textarea></label>
                <button class="btn btn-warn" type="submit"><x-icon name="message" /> ขอข้อมูลเพิ่ม</button>
            </form>
            <script>
                (() => {
                    const form = document.getElementById('camera');
                    const decision = document.getElementById('officer-decision');
                    const rejectSheet = document.getElementById('officer-reject-sheet');
                    const submit = document.getElementById('officer-submit');
                    const rejectReason = form.querySelector('[name=reject_reason]');
                    form.querySelectorAll('[data-officer-decision]').forEach((button) => {
                        button.addEventListener('click', () => {
                            decision.value = button.dataset.officerDecision;
                            submit.hidden = false;
                            form.querySelectorAll('[data-officer-decision]').forEach((item) => item.classList.toggle('is-picked', item === button));
                            if (decision.value === 'reject') {
                                rejectSheet.hidden = false;
                                rejectReason.required = true;
                                rejectReason.focus();
                                submit.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.2 20.2 19H3.8L12 4.2z"/><path d="M12 10v4M12 16.5h.01"/></svg> ยืนยันไม่เห็นชอบคำขอ';
                                submit.className = 'btn btn-danger';
                            } else {
                                rejectSheet.hidden = true;
                                rejectReason.required = false;
                                rejectReason.value = '';
                                submit.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.2l2.4 2.4L16.2 9"/></svg> ยืนยันเห็นชอบและส่งผู้อำนวยการกอง';
                                submit.className = 'btn btn-success';
                            }
                        });
                    });
                })();
            </script>
        @endif

        @if ($isOfficer && $viewingRequest->status === \App\Enums\RequestStatus::Approved)
            <form id="schedule" method="POST" action="{{ route('requests.schedule', $viewingRequest) }}" class="action-panel tone-schedule">
                @csrf
                <div class="action-panel-head">
                    <span class="action-panel-icon"><x-icon name="calendar" /></span>
                    <div>
                        <h2>เปิดนัดดูภาพ</h2>
                        <p>กำหนดวันเวลา ห้อง และผู้ที่จะเข้าห้องควบคุม</p>
                    </div>
                </div>
                <label class="field">วันเวลา<input type="datetime-local" name="appointment_at" required></label>
                <label class="field">ห้องควบคุม<input name="appointment_place" value="ห้องควบคุมความปลอดภัย คณะวิทยาศาสตร์" required></label>
                <label class="field">ผู้ที่จะเข้าห้อง<input name="participants" required></label>
                <button class="btn btn-info" type="submit"><x-icon name="calendar" /> บันทึกนัดหมาย</button>
            </form>
        @endif

        @if ($isOfficer && $viewingRequest->status === \App\Enums\RequestStatus::Scheduled && $viewingRequest->appointment_confirmed_at)
            <form method="POST" action="{{ route('requests.viewing', $viewingRequest) }}" class="action-panel tone-view">
                @csrf
                <div class="action-panel-head">
                    <span class="action-panel-icon"><x-icon name="eye" /></span>
                    <div>
                        <h2>บันทึกหลังการดูภาพ</h2>
                        <p>บันทึกช่วงเวลาและข้อสรุปหลังดูภาพภายใต้การควบคุม</p>
                    </div>
                </div>
                <div class="grid-2">
                    <label class="field">เริ่ม<input type="datetime-local" name="viewing_started_at" required></label>
                    <label class="field">สิ้นสุด<input type="datetime-local" name="viewing_ended_at" required></label>
                </div>
                <label class="field">ข้อสรุป<textarea name="viewing_summary" required></textarea></label>
                <label class="check"><input type="checkbox" name="controlled" value="1" required> <span>ยืนยันว่าควบคุมการดูภาพแล้ว ไม่มีการถ่ายภาพหน้าจอ อัดวิดีโอ หรือผู้ที่ไม่เกี่ยวข้อง</span></label>
                <button class="btn btn-teal" type="submit"><x-icon name="eye" /> บันทึกการดูภาพ</button>
            </form>
        @endif

        @if ($isOfficer && in_array($viewingRequest->status, [\App\Enums\RequestStatus::Viewed, \App\Enums\RequestStatus::NoFootage, \App\Enums\RequestStatus::Rejected], true))
            <form id="close-request" method="POST" action="{{ route('requests.close', $viewingRequest) }}" class="action-panel tone-close">
                @csrf
                <div class="action-panel-head">
                    <span class="action-panel-icon"><x-icon name="flag" /></span>
                    <div>
                        <h2>ปิดคำขอ</h2>
                        <p>ขั้นตอนสุดท้ายทำโดยเจ้าหน้าที่ผู้รับผิดชอบคำขอเท่านั้น เมื่อปิดแล้วผู้ยื่นจะเห็นสถานะ “ปิดเรื่อง”</p>
                    </div>
                </div>
                <button class="btn btn-slate" type="submit"><x-icon name="check" /> ปิดคำขอฉบับนี้</button>
            </form>
        @endif
    </article>

    @push('styles')
    <style>
        .request-show { overflow: hidden; }
        .request-back {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 0.85rem;
        }
        .request-back-icon { transform: scaleX(-1); width: 1.05rem; height: 1.05rem; }
        .request-hero {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem 1rem;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0.85rem;
        }
        .request-kicker {
            margin: 0 0 0.2rem;
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 600;
        }
        .request-hero h1 { margin: 0 0 0.35rem; font-size: 1.55rem; }
        .request-next { margin: 0; color: var(--muted); max-width: 42rem; }

        .appointment-spotlight {
            margin: 0.35rem 0 1.15rem;
            padding: 1.25rem 1.2rem 1.15rem;
            border-radius: 18px;
            border: 2px solid #7dd3fc;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.18), transparent 42%),
                linear-gradient(180deg, #e0f2fe 0%, #f0f9ff 48%, #ffffff 100%);
            box-shadow: 0 14px 28px rgba(3, 105, 161, 0.12);
        }
        .appointment-spotlight-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0 0 0.45rem;
            color: #0369a1;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .appointment-spotlight-kicker .icon { width: 1.1rem; height: 1.1rem; }
        .appointment-spotlight-when {
            margin: 0 0 0.95rem;
            font-size: clamp(1.45rem, 3.2vw, 1.95rem);
            line-height: 1.3;
            color: #0c4a6e;
            font-weight: 700;
        }
        .appointment-spotlight-facts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 1rem;
            margin: 0 0 1rem;
        }
        .appointment-spotlight-facts div {
            padding: 0.8rem 0.9rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #bae6fd;
        }
        .appointment-spotlight-facts dt {
            margin: 0 0 0.2rem;
            color: #0369a1;
            font-size: 0.88rem;
            font-weight: 700;
        }
        .appointment-spotlight-facts dd {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.45;
            color: var(--ink);
            word-break: break-word;
        }
        .appointment-spotlight-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            align-items: center;
        }
        @media (max-width: 720px) {
            .appointment-spotlight-facts { grid-template-columns: 1fr; }
        }

        .review-decision-zone {
            display: grid;
            gap: 0.85rem;
            margin: 1rem 0 1.15rem;
            padding: 0.95rem;
            border-radius: 16px;
            border: 1px solid #c7d2fe;
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 55%);
        }
        .review-stack { display: grid; gap: 0.75rem; margin: 1rem 0; }
        .review-decision-zone .review-stack { margin: 0; }
        .review-stack-head h2 { margin: 0 0 0.25rem; font-size: 1.12rem; }
        .review-stack-head p { margin: 0 0 0.35rem; color: var(--muted); }
        .review-card {
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            overflow: hidden;
        }
        .review-card-head {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
            padding: 0.85rem 0.95rem 0.7rem;
            border-bottom: 1px solid #f3e4e1;
        }
        .review-card-head h3 { margin: 0 0 0.25rem; font-size: 1rem; }
        .review-card-ico {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.7rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
        }
        .review-who {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.3rem;
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 600;
        }
        .review-who .icon { width: 0.95rem; height: 0.95rem; }
        .review-who a { color: var(--help); font-weight: 700; }
        .review-card-body {
            padding: 0.85rem 0.95rem 0.95rem;
            display: grid;
            gap: 0.65rem;
        }
        .review-fact span,
        .review-note span {
            display: block;
            color: var(--muted);
            font-size: 0.86rem;
            font-weight: 700;
            margin-bottom: 0.15rem;
        }
        .review-fact strong { font-weight: 700; }
        .review-note p {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.55;
        }
        .review-card.tone-officer { border-color: #fcd34d; }
        .review-card.tone-officer .review-card-head { background: #fffbeb; }
        .review-card.tone-officer .review-card-ico { background: #d97706; }
        .review-card.tone-director { border-color: #c7d2fe; }
        .review-card.tone-director .review-card-head { background: #eef2ff; }
        .review-card.tone-director .review-card-ico { background: #4f46e5; }
        .review-card.tone-dean { border-color: #ddd6fe; }
        .review-card.tone-dean .review-card-head { background: #f5f3ff; }
        .review-card.tone-dean .review-card-ico { background: #7c3aed; }
        .review-decision-zone .action-panel { margin-top: 0; }

        .progress-board {
            margin: 0.35rem 0 1.1rem;
            padding: 1.1rem 1.15rem 1rem;
            border-radius: 16px;
            border: 1px solid #fecdd3;
            background:
                radial-gradient(circle at top right, rgba(251, 113, 133, 0.14), transparent 40%),
                linear-gradient(180deg, #fff1f2 0%, #ffffff 48%, #eef2ff 100%);
            box-shadow: 0 12px 28px rgba(159, 18, 57, 0.06);
        }
        .progress-board-head {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem 1rem;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0.9rem;
        }
        .progress-kicker {
            margin: 0 0 0.2rem;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 700;
        }
        .progress-board-head h2 {
            margin: 0 0 0.35rem;
            font-size: 1.22rem;
        }
        .progress-next {
            margin: 0;
            color: #9f1239;
            font-weight: 600;
            max-width: 36rem;
            line-height: 1.45;
        }
        .progress-badge {
            display: flex;
            gap: 0.65rem;
            align-items: center;
            min-width: 12rem;
            padding: 0.7rem 0.85rem;
            border-radius: 14px;
            border: 1px solid transparent;
            background: #fff;
        }
        .progress-badge strong { display: block; font-size: 0.95rem; }
        .progress-badge small { color: var(--muted); }
        .progress-badge-ico {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.75rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
            background: #e11d48;
        }
        .progress-badge.tone-amber { border-color: #fcd34d; background: #fffbeb; }
        .progress-badge.tone-amber .progress-badge-ico { background: #d97706; }
        .progress-badge.tone-orange { border-color: #fdba74; background: #fff7ed; }
        .progress-badge.tone-orange .progress-badge-ico { background: #ea580c; }
        .progress-badge.tone-indigo { border-color: #c7d2fe; background: #eef2ff; }
        .progress-badge.tone-indigo .progress-badge-ico { background: #4f46e5; }
        .progress-badge.tone-violet { border-color: #ddd6fe; background: #f5f3ff; }
        .progress-badge.tone-violet .progress-badge-ico { background: #7c3aed; }
        .progress-badge.tone-teal { border-color: #99f6e4; background: #f0fdfa; }
        .progress-badge.tone-teal .progress-badge-ico { background: #0d9488; }
        .progress-badge.tone-emerald { border-color: #a7f3d0; background: #ecfdf5; }
        .progress-badge.tone-emerald .progress-badge-ico { background: #059669; }
        .progress-badge.tone-sky { border-color: #bae6fd; background: #f0f9ff; }
        .progress-badge.tone-sky .progress-badge-ico { background: #0284c7; }
        .progress-badge.tone-cyan { border-color: #a5f3fc; background: #ecfeff; }
        .progress-badge.tone-cyan .progress-badge-ico { background: #0891b2; }
        .progress-badge.tone-slate { border-color: #cbd5e1; background: #f8fafc; }
        .progress-badge.tone-slate .progress-badge-ico { background: #475569; }
        .progress-badge.tone-rose { border-color: #fecdd3; background: #fff1f2; }
        .progress-badge.tone-rose .progress-badge-ico { background: #e11d48; }
        .progress-badge.tone-stone { border-color: #d6d3d1; background: #fafaf9; }
        .progress-badge.tone-stone .progress-badge-ico { background: #78716c; }

        .progress-meter {
            height: 0.55rem;
            border-radius: 999px;
            background: #ffe4e6;
            overflow: hidden;
            margin-bottom: 0.95rem;
        }
        .progress-meter-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #e11d48, #4f46e5);
            transition: width 0.25s ease;
        }

        .progress-steps {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.55rem;
        }
        .progress-step {
            display: grid;
            gap: 0.55rem;
            justify-items: start;
            padding: 0.8rem 0.75rem;
            border-radius: 14px;
            border: 1px solid #f3e4e1;
            background: rgba(255, 255, 255, 0.92);
            min-width: 0;
        }
        .progress-step-ico {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #f8efec;
            color: var(--muted);
        }
        .progress-step-ico .icon { width: 1.15rem; height: 1.15rem; }
        .progress-step-copy {
            display: grid;
            gap: 0.12rem;
            min-width: 0;
        }
        .progress-step-copy small {
            color: var(--muted);
            font-weight: 700;
            font-size: 0.78rem;
        }
        .progress-step-copy strong {
            font-size: 0.95rem;
            line-height: 1.3;
        }
        .progress-step-copy span {
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.35;
        }
        .progress-now {
            display: inline-flex;
            align-items: center;
            padding: 0.18rem 0.55rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .progress-now.is-done {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .progress-now.is-wait {
            background: #f8fafc;
            color: #64748b;
            border-color: #cbd5e1;
        }
        .progress-now.is-stopped {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fdba74;
        }
        .progress-step.is-done {
            border-color: #a7f3d0;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 70%);
        }
        .progress-step.is-done .progress-step-ico {
            background: #059669;
            color: #fff;
        }
        .progress-step.is-current {
            border-color: #fda4af;
            background: linear-gradient(180deg, #fff1f2 0%, #ffffff 70%);
            box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.12);
        }
        .progress-step.is-current .progress-step-ico {
            background: #e11d48;
            color: #fff;
            box-shadow: 0 8px 16px rgba(225, 29, 72, 0.25);
        }
        .progress-step.is-stopped {
            border-color: #fdba74;
            background: linear-gradient(180deg, #fff7ed 0%, #ffffff 70%);
        }
        .progress-step.is-stopped .progress-step-ico {
            background: #ea580c;
            color: #fff;
        }
        .progress-step.is-wait {
            opacity: 0.78;
        }

        @media (max-width: 980px) {
            .progress-steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .progress-steps { grid-template-columns: 1fr; }
        }

        .request-footnote {
            display: flex;
            gap: 0.45rem;
            align-items: flex-start;
            margin: 1rem 0 0;
            color: var(--muted);
        }
        .request-footnote .icon { width: 1.05rem; height: 1.05rem; margin-top: 0.2rem; color: var(--help); }

        .detail-block {
            margin: 1rem 0;
            padding: 1.05rem 1.1rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
        }
        .detail-block.tone-sand { border-color: #fcd34d; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 55%); }
        .detail-block.tone-blue { border-color: #bfdbfe; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 55%); }
        .detail-block.tone-rose { border-color: #fecdd3; background: linear-gradient(180deg, #fff1f2 0%, #ffffff 38%); }
        .detail-block-head {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            margin-bottom: 0.95rem;
        }
        .detail-block-head h2 { margin: 0 0 0.2rem; font-size: 1.12rem; }
        .detail-block-head p { margin: 0; color: var(--muted); }
        .detail-block-icon {
            width: 2.45rem;
            height: 2.45rem;
            border-radius: 0.8rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
            background: #e11d48;
        }
        .detail-block.tone-sand .detail-block-icon { background: #d97706; }
        .detail-block.tone-blue .detail-block-icon { background: #2563eb; }
        .detail-sections { display: grid; gap: 0.85rem; }
        .detail-section {
            padding: 0.9rem 0.95rem;
            border-radius: 12px;
            border: 1px solid #f3e4e1;
            background: rgba(255, 255, 255, 0.88);
        }
        .detail-section h3 {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0 0 0.7rem;
            font-size: 1rem;
            color: #9f1239;
        }
        .detail-section h3 .icon { width: 1.05rem; height: 1.05rem; }
        .detail-facts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem 0.9rem;
        }
        .detail-facts > div {
            min-width: 0;
            display: grid;
            gap: 0.18rem;
        }
        .detail-facts span {
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 600;
        }
        .detail-facts strong {
            font-weight: 700;
            line-height: 1.45;
            word-break: break-word;
        }
        .detail-facts .span-2 { grid-column: 1 / -1; }
        .detail-prose {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.65;
            color: var(--ink);
        }
        .detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.9rem;
        }
        .detail-time {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 0.75rem;
            align-items: stretch;
        }
        .detail-time > div:not(.detail-time-divider) {
            padding: 0.85rem 0.9rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #fecdd3;
            display: grid;
            gap: 0.25rem;
        }
        .detail-time span { color: var(--muted); font-size: 0.9rem; font-weight: 600; }
        .detail-time strong { line-height: 1.45; }
        .detail-time-divider {
            align-self: center;
            color: var(--help);
            font-weight: 700;
            font-size: 0.92rem;
        }
        .detail-time-summary {
            margin: 0.7rem 0 0;
            color: #9f1239;
            font-weight: 600;
        }
        .urgency-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.92rem;
        }
        .urgency-pill.is-normal { background: #ecfdf5; color: #047857; }
        .urgency-pill.is-urgent { background: #fff1f2; color: #be123c; }

        @media (max-width: 720px) {
            .detail-facts,
            .detail-time { grid-template-columns: 1fr; }
            .detail-time-divider { text-align: center; }
        }

        .action-panel {
            margin-top: 1.25rem;
            padding: 1.1rem 1.15rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
        }
        .action-panel-head {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            margin-bottom: 0.95rem;
        }
        .action-panel-head h2 { margin: 0 0 0.25rem; font-size: 1.12rem; }
        .action-panel-head p { margin: 0; color: var(--muted); }
        .action-panel-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.8rem;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
        }
        .action-panel.tone-check { border-color: #fcd34d; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 55%); }
        .action-panel.tone-check .action-panel-icon { background: #d97706; }
        .action-panel.tone-wait { border-color: #fdba74; background: linear-gradient(180deg, #fff7ed 0%, #ffffff 55%); }
        .action-panel.tone-wait .action-panel-icon { background: #ea580c; }
        .action-panel.tone-schedule { border-color: #bfdbfe; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 55%); }
        .action-panel.tone-schedule .action-panel-icon { background: #2563eb; }
        .action-panel.tone-view { border-color: #99f6e4; background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 55%); }
        .action-panel.tone-view .action-panel-icon { background: #0d9488; }
        .action-panel.tone-close { border-color: #cbd5e1; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 55%); }
        .action-panel.tone-close .action-panel-icon { background: #475569; }
        .action-panel.tone-review { border-color: #c7d2fe; background: linear-gradient(180deg, #eef2ff 0%, #ffffff 55%); }
        .action-panel.tone-review .action-panel-icon { background: #4f46e5; }

        .btn-danger { background: #be123c; color: #fff; }
        .btn-danger:hover { background: #9f1239; }
        .btn-info { background: #2563eb; color: #fff; }
        .btn-info:hover { background: #1d4ed8; }
        .btn-teal { background: #0d9488; color: #fff; }
        .btn-teal:hover { background: #0f766e; }
        .btn-slate { background: #475569; color: #fff; }
        .btn-slate:hover { background: #334155; }
        .btn.is-picked { box-shadow: 0 0 0 3px rgba(253, 164, 175, 0.55); transform: translateY(-1px); }
        .decision-bar .btn { min-width: 10.5rem; }
    </style>
    @endpush
@endsection
