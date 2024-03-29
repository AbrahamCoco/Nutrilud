<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tusuario_nutriologo;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imageUrl = asset('images/' . $imageName);

            // Aquí puedes guardar $imageUrl en tu base de datos

            return response()->json([
                'status' => true,
                'message' => 'Imagen subida con éxito',
                'url' => $imageUrl,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'No se ha subido ninguna imagen',
        ], 400);
    }

    public function register(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users',
            'correo' => 'required|string|email|max:255|unique:users',
            'contrasenia' => 'required|string|min:8',
            'trol_id' => 'required|int',
        ]);

        // Si la validación falla, devolver un mensaje de error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Fallo al validar los datos',
                'errors' => $validator->errors()
            ], 400);
        }

        // Crear un nuevo usuario
        $user = User::create([
            'trol_id' => $request->trol_id,
            'nombre' => $request->nombre,
            'primer_apellido' => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'usuario' => $request->usuario,
            'correo' => $request->correo,
            'contrasenia' => Hash::make($request->contrasenia),
            'estado' => 1,
        ]);

        // Variable para almacenar los datos del tipo de usuario creado
        $tipoUsuarioData = null;

        // Agregar el tipo de usuario al usuario
        switch ($request->trol_id) {
            case 1:
                $tipoUsuarioData = $user->admin()->create([
                    'descripcion' => $request->descripcion,
                    'foto' => $request->foto,
                    'telefono' => $request->telefono,
                ]);
                break;
            case 2:
                $tipoUsuarioData = $user->nutriologo()->create([
                    'descripcion' => $request->descripcion,
                    'foto' => $request->foto,
                    'direccion' => $request->direccion,
                    'telefono' => $request->telefono,
                    'cedula_profesional' => $request->cedula_profesional,
                ]);
                break;
            case 3:
                $tipoUsuarioData = $user->paciente()->create([
                    'foto' => $request->foto,
                    'telefono' => $request->telefono,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'sexo' => $request->sexo,
                    'alergias' => $request->alergias,
                ]);
                break;
            default:
                throw new Exception('Tipo de usuario no válido');
        }

        // Generar un token para el usuario
        $token = $user->createToken('Nutrilud')->plainTextToken;

        // Devolver una respuesta exitosa
        return response()->json([
            'status' => true,
            'message' => 'Usuario creado con éxito',
            'token' => $token,
            'user' => $user,
            'tipo_usuario' => $tipoUsuarioData, // Agrega los datos del tipo de usuario aquí
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            $validatorUser = Validator::make(
                request()->all(),
                [
                    'usuario' => 'required',
                    'contrasenia' => 'required'
                ]
            );

            if ($validatorUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Fallo al validar los datos',
                    'errors' => $validatorUser->errors()
                ], 401);
            }

            $user = User::where('usuario', $request->usuario)->first();

            if ($user && Hash::check($request->contrasenia, $user->contrasenia)) {
                // Las credenciales son correctas, generar token
                $token = $user->createToken('Nutrilud')->plainTextToken;

                // Devolver una respuesta exitosa
                return response()->json([
                    'status' => true,
                    'message' => 'Inicio de sesión exitoso',
                    'token' => $token,
                    'user' => $user->id,
                    'admin_id' => $user->admin->id ?? null,
                    'nutriologo_id' => $user->nutriologo->id ?? null,
                    'paciente_id' => $user->paciente->id ?? null,
                    'trol_id' => $user->trol_id,
                ], 200);
            } else {
                // Las credenciales no son correctas, devolver error
                return response()->json([
                    'status' => false,
                    'message' => 'Usuario y contraseña incorrectos'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $user->tokens->each(function ($token, $key) {
                    $token->delete();
                });

                return response()->json([
                    'status' => true,
                    'message' => 'Sesión cerrada con éxito'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No hay ninguna sesión activa'
                ], 400);
            }
        } catch (\Exception $exception) {
            logger()->error($exception->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Ocurrió un error al cerrar la sesión'
            ], 500);
        }
    }

    public function showDatos($id)
    {
        try {
            $nutriologoData = Tusuario_nutriologo::with('user')->find($id);

            // Utiliza response()->json() para enviar la respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Datos del nutriologo',
                'data' => $nutriologoData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del nutriologo',
                'data' => $th
            ], 500);
        }
    }
}
