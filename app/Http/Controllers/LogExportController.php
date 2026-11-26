<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $query = DB::table('log_trangthai')
            ->select([
                'malog_tt',
                'ma_doituong',
                'loai_doituong',
                'trangthai_dh',
                'trangthai_tt',
                'created_at',
                'updated_at',
            ]);

        // Lọc theo loại đối tượng: DON_HANG hoặc GIAO_DICH_QR
        if ($request->filled('loai_doituong')) {
            $loai = strtoupper(trim($request->string('loai_doituong')->toString()));
            if (in_array($loai, ['DON_HANG', 'GIAO_DICH_QR'], true)) {
                $query->where('loai_doituong', $loai);
            }
        }

        // Lọc theo mã đối tượng (madh hoặc mã tra cứu QR)
        if ($request->filled('ma_doituong')) {
            $ma = trim($request->string('ma_doituong')->toString());
            $query->where('ma_doituong', 'like', '%' . $ma . '%');
        }

        // Lọc theo khoảng ngày (tận dụng index idx_log_created_at)
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->date('from')->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->date('to')->endOfDay());
        }

        $fileName = 'log-trangthai-' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM để Excel hiển thị đúng tiếng Việt
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Mã log',
                'Mã đối tượng',
                'Loại đối tượng',
                'Trạng thái đơn hàng',
                'Trạng thái thanh toán',
                'Ngày tạo',
                'Ngày cập nhật',
            ]);

            $query->orderByDesc('malog_tt')->chunkById(1000, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        (int) $row->malog_tt,
                        $this->sanitizeCsv((string) ($row->ma_doituong ?? '')),
                        $this->sanitizeCsv((string) ($row->loai_doituong ?? '')),
                        $this->sanitizeCsv((string) ($row->trangthai_dh ?? '')),
                        $this->sanitizeCsv((string) ($row->trangthai_tt ?? '')),
                        (string) ($row->created_at ?? ''),
                        (string) ($row->updated_at ?? ''),
                    ]);
                }
            }, 'malog_tt');

            fclose($handle);
        }, $fileName, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, private',
        ]);
    }

    private function sanitizeCsv(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}