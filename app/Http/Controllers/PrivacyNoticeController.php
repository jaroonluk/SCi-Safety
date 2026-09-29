<?php

namespace App\Http\Controllers;

use App\Models\PrivacyNotice;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacyNoticeController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function show(): View
    {
        return view('privacy.show', ['notice' => PrivacyNotice::current()]);
    }

    public function edit(): View
    {
        return view('privacy.edit', [
            'notice' => PrivacyNotice::current(),
            'history' => PrivacyNotice::query()->latest('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:40', 'unique:privacy_notices,version'],
            'body' => ['required', 'string', 'max:8000'],
        ]);

        $notice = PrivacyNotice::query()->create([
            'version' => $data['version'],
            'body' => $data['body'],
            'published_by' => $request->user()->id,
        ]);
        $this->audit->record($request->user(), 'privacy.published', [
            'version' => $notice->version,
        ], $request, $notice->getMorphClass(), $notice->id);

        return back()->with('status', 'เผยแพร่ประกาศความเป็นส่วนตัวรุ่น '.$notice->version.' แล้ว ข้อความเดิมยังถูกเก็บไว้');
    }
}
