<?php

namespace App\Http\Controllers;

use App\Models\CrmEmployeeTraining;
use Spatie\Browsershot\Browsershot;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicCertificateController extends Controller
{
    public function show(string $token)
    {
        $training = CrmEmployeeTraining::with('client')
            ->where('public_token', $token)
            ->firstOrFail();

        return view('public_certificate', compact('training', 'token'));
    }

    public function download(string $token)
    {
        $training = CrmEmployeeTraining::with('client')
            ->where('public_token', $token)
            ->firstOrFail();

        $template = 'admin.training.certificate_' . max(1, min(3, (int) $training->certificate_template));

        $logoPath = public_path('images/logo.png');
        $logoB64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $sigPath  = storage_path('app/public/certificate/signature.png');
        $sigB64   = file_exists($sigPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath))
            : null;

        $stampPath = storage_path('app/public/certificate/stamp.png');
        $stampB64  = file_exists($stampPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))
            : null;

        $verifyUrl = route('certificate.verify', $training->id);
        $qrSvg     = QrCode::format('svg')->size(80)->errorCorrection('M')->generate($verifyUrl);
        $qrB64     = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $html = view($template, compact('training', 'logoB64', 'sigB64', 'stampB64', 'qrB64', 'verifyUrl'))->render();

        $filename = 'BA-CERT-' . $training->id . '-' . $training->training_date->format('Ymd') . '.pdf';

        putenv('HOME=/tmp');

        $pdf = Browsershot::html($html)
            ->setChromePath('/usr/bin/google-chrome-stable')
            ->setNodeModulePath('/usr/lib/node_modules')
            ->addChromiumArguments(['disable-dev-shm-usage', 'disable-gpu', 'no-zygote'])
            ->format('A4')
            ->landscape()
            ->noSandbox()
            ->showBackground()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
