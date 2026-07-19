<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\AiCopilot\Services\FinancialRiskAnalyzer;
use App\Modules\AiCopilot\Services\FinancialSnapshotService;
use App\Modules\AiCopilot\Services\OpenAiCopilotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class AiCopilotController extends Controller
{
    private const IMPERSONATION_MESSAGE = 'لا يمكن إجراء التحليل المالي أثناء انتحال الحساب.';

    private const SERVICE_UNAVAILABLE_MESSAGE = 'تعذر إكمال التحليل المالي الآن. يرجى المحاولة لاحقاً.';

    public function analyze(
        Request $request,
        FinancialSnapshotService $snapshotService,
        FinancialRiskAnalyzer $riskAnalyzer,
        OpenAiCopilotService $openAiCopilotService,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        if ($request->session()->has('impersonator_id')) {
            return response()->json(['message' => self::IMPERSONATION_MESSAGE], 403);
        }

        $snapshot = $snapshotService->build($user);
        $riskAnalysis = $riskAnalyzer->analyze($snapshot);

        if ($riskAnalysis['state'] === 'insufficient_data') {
            return response()->json($this->insufficientDataResult());
        }

        try {
            $result = $openAiCopilotService->generateInsights($snapshot, $riskAnalysis);
        } catch (RuntimeException) {
            return response()->json(['message' => self::SERVICE_UNAVAILABLE_MESSAGE], 503);
        }

        return response()->json($result);
    }

    private function insufficientDataResult(): array
    {
        return [
            'health_status' => 'insufficient_data',
            'summary_ar' => 'لا تتوفر بيانات مالية كافية لإجراء تحليل مفيد حالياً.',
            'insights' => [],
            'actions' => [],
            'limitations_ar' => [
                'يحتاج التحليل إلى نشاط مالي مسجل مثل المعاملات أو الأرصدة أو الفواتير أو الديون.',
            ],
            'disclaimer_ar' => 'هذه إرشادات تشغيلية تعليمية وليست نصيحة محاسبية أو استثمارية أو ضريبية أو قانونية.',
        ];
    }
}
