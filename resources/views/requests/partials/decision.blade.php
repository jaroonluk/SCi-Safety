@php
    $escalateLabel = $allowEscalate ?? false;
    $rejectHint = $escalateLabel
        ? 'อนุมัติหรือส่งต่อรองคณบดีได้เลย หากไม่อนุมัติต้องระบุเหตุผล'
        : 'อนุมัติได้เลย หากไม่อนุมัติต้องระบุเหตุผล (จะเห็นข้อความเจ้าหน้าที่และผู้อำนวยการกองด้านบน)';
@endphp
<form method="POST" action="{{ route('requests.decide', $viewingRequest) }}" class="decision-dock action-panel tone-review">
    @csrf
    <input type="hidden" name="decision" value="">
    <div class="action-panel-head">
        <span class="action-panel-icon"><x-icon name="scale" /></span>
        <div>
            <h2>พิจารณาคำขอ</h2>
            <p>{{ $rejectHint }}</p>
        </div>
    </div>
    <div class="decision-bar">
        <button class="btn btn-success" type="button" data-decision="approve"><x-icon name="check" /> อนุมัติ</button>
        @if ($escalateLabel)
            <button class="btn btn-info" type="button" data-decision="escalate"><x-icon name="arrow" /> ส่งรองคณบดีฝ่ายบริหาร</button>
        @endif
        <button class="btn btn-danger" type="button" data-decision="reject"><x-icon name="alert" /> ไม่อนุมัติ</button>
    </div>
    <div class="sheet" hidden>
        <label class="field">
            <span data-comment-label>เหตุผล / ข้อความถึงผู้เกี่ยวข้อง (ถ้ามี)</span>
            <textarea name="comment" placeholder="เช่น อนุมัติให้นัดดูเฉพาะช่วงเวลาที่ระบุ / ส่งต่อเพราะเป็นเรื่องเร่งด่วน / ไม่อนุมัติเพราะ…"></textarea>
        </label>
        <button class="btn btn-solid" type="submit"><x-icon name="check" /> ยืนยันคำวินิจฉัย</button>
    </div>
</form>
<script>
    (() => {
        const form = document.currentScript.previousElementSibling;
        if (! form || form.tagName !== 'FORM') return;
        const comment = form.querySelector('[name=comment]');
        const label = form.querySelector('[data-comment-label]');
        form.querySelectorAll('[data-decision]').forEach((button) => button.addEventListener('click', () => {
            const decision = button.dataset.decision;
            form.querySelector('[name=decision]').value = decision;
            form.querySelector('.sheet').hidden = false;
            form.querySelectorAll('[data-decision]').forEach((item) => item.classList.toggle('is-picked', item === button));
            const needsReason = decision === 'reject';
            comment.required = needsReason;
            label.textContent = needsReason
                ? 'เหตุผลที่ไม่อนุมัติ'
                : 'เหตุผล / ข้อความถึงผู้เกี่ยวข้อง (ถ้ามี)';
            comment.focus();
        }));
    })();
</script>
