<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use App\Models\Order;
use Exception;
use Illuminate\Http\UploadedFile;

class PaymentProofService
{
    /**
     * Dapatkan path absolut direktori bukti transfer (mendukung direktori cPanel public_html)
     */
    public function getBuktiTfPath(): string
    {
        $publicHtmlPath = base_path('../public_html');

        if (file_exists($publicHtmlPath)) {
            return $publicHtmlPath.'/img/buktitf';
        }

        return public_path('img/buktitf');
    }

    /**
     * Scan riwayat berkas bukti transfer berdasarkan nomor order
     */
    public function getProofHistoryFiles(string $orderNumber): array
    {
        $targetFolder = $this->getBuktiTfPath();

        if (! file_exists($targetFolder)) {
            return [];
        }

        $pattern = $targetFolder.'/'.$orderNumber.'-*.*';
        $files = glob($pattern);

        if (empty($files) || ! is_array($files)) {
            return [];
        }

        sort($files);

        $historyFormatted = [];
        foreach ($files as $index => $filePath) {
            $fileName = basename($filePath);
            $historyFormatted[] = [
                'url' => asset('img/buktitf/'.$fileName),
                'file' => $fileName,
                'sequence' => $index + 1,
                'uploaded_at' => date('d M Y, H:i', filemtime($filePath)).' WIB',
            ];
        }

        return $historyFormatted;
    }

    /**
     * Simpan file unggahan bukti transfer baru
     */
    public function storeProof(Order $order, UploadedFile $file): string
    {
        $targetFolder = $this->getBuktiTfPath();

        if (! file_exists($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }

        $existingHistory = $this->getProofHistoryFiles($order->order_number);
        $nextSequence = count($existingHistory) + 1;
        $sequenceStr = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

        $fileName = $order->order_number.'-'.$sequenceStr.'.webp';
        $destinationPath = $targetFolder.'/'.$fileName;

        try {
            ImageHelper::convertToWebp($file->getRealPath(), $destinationPath, 80, 1200);
        } catch (Exception $e) {
            $fileName = $order->order_number.'-'.$sequenceStr.'.'.$file->getClientOriginalExtension();
            $file->move($targetFolder, $fileName);
        }

        return $fileName;
    }
}
