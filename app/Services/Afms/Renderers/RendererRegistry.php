<?php

namespace App\Services\Afms\Renderers;

/**
 * Central registry for all template renderers.
 *
 * To add a new template type:
 *   1. Create your renderer in App\Services\Afms\Renderers\
 *   2. Add the template key => renderer class to $rendererMap
 *   3. Add the template key => output file prefix to $templateMap
 *
 * Keys must be substrings of the template filename.
 * Example: key 'pr_meal' matches 'pr_meal_template.xlsx'
 */
class RendererRegistry
{
    // ==================== ADD NEW TYPES HERE ====================

    public static array $templateMap = [
        // PR types
        'pr_meal'             => 'PR_MEAL',
        'pr_service'          => 'PR_SERVICE',
        'pr_transportation'   => 'PR_TRANSPORTATION',
        'pr_workbook'         => 'PR_WORKBOOK',
        'pr_admin_janitorial' => 'PR_ADMIN_JANITORIAL',

        // PO types (add when ready)
        // 'po_meal'    => 'PO_MEAL',
        // 'po_service' => 'PO_SERVICE',
    ];

    public static array $rendererMap = [
        // PR types
        'pr_meal'             => MealBlockRenderer::class,
        'pr_workbook'         => WorkbookBlockRenderer::class,
        'pr_transportation'   => TransportationBlockRenderer::class,
        'pr_service'          => ServiceBlockRenderer::class,
        'pr_admin_janitorial' => AdminJanitorialBlockRenderer::class,

        // PO types (add when ready)
        // 'po_meal'    => MealBlockRenderer::class,
        // 'po_service' => ServiceBlockRenderer::class,
    ];

    // ============================================================
}
