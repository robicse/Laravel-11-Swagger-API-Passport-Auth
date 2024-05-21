<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(title="API Documentation", version="1.0.1")
 */

class ApiController extends Controller
{
    // POST [ name, email, password ]
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Register"},
     *     operationId="Register",
     *     summary="Register a new User",
     *     description="This endpoint allows you to register a new user.",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"name","email","password","password_confirmation"},
     *                  @OA\Property(property="name", type="string", example="Robi"),
     *                  @OA\Property(property="email", type="string", example="robicse8@gmail.com"),
     *                  @OA\Property(property="password", type="string", format="password", example="12345678"),
     *                  @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *              )
     *         ),
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"name","email","password","password_confirmation"},
     *                  @OA\Property(property="name", type="string", example="Robi"),
     *                  @OA\Property(property="email", type="string", example="robicse8@gmail.com"),
     *                  @OA\Property(property="password", type="string", format="password", example="12345678"),
     *                  @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unprocessable Entity"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {

        // Validation
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed" // password_confirmation
        ]);

        // Create User
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);

        return response()->json([
            "status" => true,
            "message" => "User registered successfully",
            "data" => []
        ]);
    }

    // POST [ email, password ]
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Login"},
     *     operationId="Login",
     *     summary="Login a new User",
     *     description="This endpoint allows you to login a new user.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"email","password"},
     *                  @OA\Property(property="email", type="text", example="robicse8@gmail.com"),
     *                  @OA\Property(property="password", type="password", example="12345678")
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User login successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User logged in successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unprocessable Entity"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *         ),
     *     )
     * )
     */
    public function login(Request $request)
    {

        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        // User object
        $user = User::where("email", $request->email)->first();

        if (!empty($user)) {

            // User exists
            if (Hash::check($request->password, $user->password)) {

                // Password matched
                $token = $user->createToken("mytoken")->accessToken;

                return response()->json([
                    "status" => true,
                    "message" => "Login successful",
                    "token" => $token,
                    "data" => []
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Password didn't match",
                    "data" => []
                ]);
            }
        } else {

            return response()->json([
                "status" => false,
                "message" => "Invalid Email value",
                "data" => []
            ]);
        }
    }

    // GET [Auth: Token]
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     operationId="profile",
     *     summary="Get User Profile",
     *     description="This endpoint allows you to retrieve the profile of the authenticated user.",
     *     security={{ "bearer":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Profile operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred"),
     *         ),
     *     )
     * )
     */

    public function profile()
    {

        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile information",
            "data" => $userData,
            "id" => auth()->user()->id
        ]);
    }

    // GET [Auth: Token]
    public function logout()
    {

        $token = auth()->user()->token();

        $token->revoke();

        return response()->json([
            "status" => true,
            "message" => "User Logged out successfully"
        ]);
    }
}
