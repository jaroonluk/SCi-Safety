@php
    $hasOfficer = $viewingRequest->technical_result || $viewingRequest->technical_note || $viewingRequest->officer_decision;
    $hasDirector = filled($viewingRequest->director_note);
    $hasDean = filled($viewingRequest->dean_note);
    $compact = $compact ?? false;
@endphp
@if ($hasOfficer || $hasDirector || $hasDean)
    <section class="review-stack {{ $compact ? 'is-compact' : '' }}" aria-label="ผลการพิจารณา">
        @unless ($compact)
            <div class="review-stack-head">
                <h2>ผลการพิจารณา</h2>
                <p>ข้อความจากเจ้าหน้าที่และผู้บริหาร อ่านประกอบก่อนตัดสินใจหรือติดตามสถานะ</p>
            </div>
        @endunless

        @if ($hasOfficer)
            <article class="review-card tone-officer">
                <div class="review-card-head">
                    <span class="review-card-ico"><x-icon name="camera" /></span>
                    <div>
                        <h3>ผลการพิจารณา · เจ้าหน้าที่ผู้รับผิดชอบคำขอ</h3>
                        @if ($viewingRequest->officerReviewer)
                            <p class="review-who">
                                <x-icon name="person" />
                                {{ $viewingRequest->officerReviewer->name }}
                                · <a href="mailto:{{ $viewingRequest->officerReviewer->email }}">{{ $viewingRequest->officerReviewer->email }}</a>
                            </p>
                        @else
                            <p class="review-who">ยังไม่ระบุอีเมลผู้พิจารณา (บันทึกก่อนระบบเก็บผู้ตรวจ)</p>
                        @endif
                    </div>
                </div>
                <div class="review-card-body">
                    @if ($viewingRequest->technical_result)
                        <div class="review-fact">
                            <span>ผลตรวจกล้อง</span>
                            <strong>{{ $viewingRequest->technical_result->label() }}</strong>
                        </div>
                    @endif
                    @if ($viewingRequest->officer_decision)
                        <div class="review-fact">
                            <span>ความเห็น</span>
                            <strong>{{ $viewingRequest->officer_decision === 'approve' ? 'เห็นชอบคำขอ' : 'ไม่เห็นชอบคำขอ' }}</strong>
                        </div>
                    @endif
                    @if ($viewingRequest->technical_note)
                        <div class="review-note">
                            <span>ข้อความถึงผู้บริหาร / ผู้ยื่น</span>
                            <p>{{ $viewingRequest->technical_note }}</p>
                        </div>
                    @endif
                </div>
            </article>
        @endif

        @if ($hasDirector)
            <article class="review-card tone-director">
                <div class="review-card-head">
                    <span class="review-card-ico"><x-icon name="scale" /></span>
                    <div>
                        <h3>ผลการพิจารณา · ผู้อำนวยการกอง</h3>
                        @if ($viewingRequest->directorReviewer)
                            <p class="review-who">
                                <x-icon name="person" />
                                {{ $viewingRequest->directorReviewer->name }}
                                · <a href="mailto:{{ $viewingRequest->directorReviewer->email }}">{{ $viewingRequest->directorReviewer->email }}</a>
                            </p>
                        @endif
                    </div>
                </div>
                <div class="review-card-body">
                    <div class="review-note">
                        <span>ข้อความ</span>
                        <p>{{ $viewingRequest->director_note }}</p>
                    </div>
                </div>
            </article>
        @endif

        @if ($hasDean)
            <article class="review-card tone-dean">
                <div class="review-card-head">
                    <span class="review-card-ico"><x-icon name="users" /></span>
                    <div>
                        <h3>ผลการพิจารณา · รองคณบดีฝ่ายบริหาร</h3>
                        @if ($viewingRequest->deanReviewer)
                            <p class="review-who">
                                <x-icon name="person" />
                                {{ $viewingRequest->deanReviewer->name }}
                                · <a href="mailto:{{ $viewingRequest->deanReviewer->email }}">{{ $viewingRequest->deanReviewer->email }}</a>
                            </p>
                        @endif
                    </div>
                </div>
                <div class="review-card-body">
                    <div class="review-note">
                        <span>ข้อความ</span>
                        <p>{{ $viewingRequest->dean_note }}</p>
                    </div>
                </div>
            </article>
        @endif
    </section>
@endif
