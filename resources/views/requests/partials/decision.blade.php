<form method="POST" action="{{ route('requests.decide', $viewingRequest) }}" class="decision-dock">
    @csrf
    <input type="hidden" name="decision" value="">
    <div class="decision-bar">
        <button class="btn btn-success" type="button" data-decision="approve">เห็นชอบ</button>
        <button class="btn btn-warn" type="button" data-decision="more_info">ขอข้อมูลเพิ่ม</button>
        @if ($allowEscalate)
            <button class="btn btn-accent" type="button" data-decision="escalate">ส่งต่อรองคณบดี</button>
        @endif
        <button class="btn btn-quiet" type="button" data-decision="reject">ไม่เห็นชอบ</button>
    </div>
    <div class="sheet" hidden>
        <label class="field">เหตุผลสั้น ๆ เพื่อให้ผู้ยื่นและเจ้าหน้าที่เข้าใจตรงกัน
            <textarea name="comment" required></textarea>
        </label>
        <button class="btn btn-solid" type="submit">ยืนยันคำวินิจฉัย</button>
    </div>
</form>
<script>
    document.querySelectorAll('[data-decision]').forEach((button) => button.addEventListener('click', () => {
        const form = button.closest('form');
        form.querySelector('[name=decision]').value = button.dataset.decision;
        form.querySelector('.sheet').hidden = false;
        form.querySelector('[name=comment]').focus();
    }));
</script>
