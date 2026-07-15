<?php

namespace App\Http\Controllers;

use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    use Searchable;

    public function index(Request $request)
    {
        $query = Activity::with('causer');

        $logs = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['log_name', 'description'],
            sortableFields: ['created_at', 'log_name', 'description'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        // Transformar os dados para o formato esperado pela tabela
        $logs->through(function ($activity) {
            return [
                'id' => $activity->id,
                'data' => $activity->created_at->format('Y-m-d'),
                'hora' => $activity->created_at->format('H:i:s'),
                'utilizador' => $activity->causer ? $activity->causer->name : 'Sistema',
                'menu' => $activity->log_name,
                'accao' => $activity->description,
                'dispositivo' => $this->parseUserAgent($activity->properties->get('user_agent')),
                'ip' => $activity->properties->get('ip', 'N/A'),
            ];
        });

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Extrai informação legível do User-Agent.
     */
    private function parseUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'N/A';
        }

        // Detetar browser
        $browser = 'Desconhecido';
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            $browser = 'Opera';
        }

        // Detetar SO
        $os = 'Desconhecido';
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return "{$browser} / {$os}";
    }
}
