<?php

namespace TrueIfNotFalse\LumenCaptcha;

use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Routing\Controller;

// <editor-fold defaultstate="collapsed" desc="▼ Swagged Documentation method /captcha ▼">
/**
 * @OA\Get(
 *     path="/captcha",
 *     tags={"Captcha"},
 *     summary="Captcha code",
 *     description="Captcha code",
 *     @OA\Parameter(
 *         name="config",
 *         in="query",
 *         required=false,
 *         example="default",
 *         @OA\Schema(
 *             type="string"
 *         )
 *      ),
 *     @OA\Response(
 *          response="200",
 *          description="Captcha code",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  @OA\Property(
 *                      property="key",
 *                      type="string",
 *                      example="$2y$10$9do9wfk6AIR.0M4wKicCOeK9wA8rIUqBrHIv2QPzcbgLS0tlK/Ch6"
 *                  ),
 *                  @OA\Property(
 *                      property="img",
 *                      type="string",
 *                      format="byte",
 *                  )
 *              )
 *          )
 *     ),
 * )
 */
// </editor-fold>

/**
 * Class CaptchaController
 *
 * @package TrueIfNotFalse\LumenCaptcha
 */
class CaptchaController extends Controller
{
    /**
     * @param Request $request
     * @param Captcha $captcha
     *
     * @return array
     */
    public function get(Request $request, Captcha $captcha): array
    {
        $config = $request->get('config', 'default');

        return $captcha->create($config);
    }
}
