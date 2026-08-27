<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Person\PersonNotFoundException;
use App\Http\Resources\PersonResource;
use App\Services\PersonService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Person Controller
 */
class PersonController extends Controller
{
    public function __construct(public readonly PersonService $personService)
    {
        //
    }

    /**
     * Get all people for a locale.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(
                PersonResource::collection(
                    $this->personService->getAllPeople($this->locale($request))
                )
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Get a specific person by slug.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $person = $this->personService->getPersonBySlug($this->locale($request), $slug);

            throw_unless($person, PersonNotFoundException::class);

            return $this->successResponse(new PersonResource($person));
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
