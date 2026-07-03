<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EQUapp | Login</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="min-h-screen flex ">
        <!-- Left Side - Image -->
        <div class="hidden lg:flex lg:w-1/3  bg-[#a9cbe0] relative overflow-hidden">
            {{-- <div class="absolute inset-0 "></div> --}}
            <div class="relative z-10 flex flex-col justify-center items-center w-full  text-white p-12 text-center">
                <div class="absolute -z-10 -right-70 w-[40rem] h-[40rem] grayscale opacity-20 h-full bg-repeat bg-cover bg-center"
                    style="background-image: url('{{ asset('logo.png') }}');"></div>
                <h1 class="text-[6rem] font-bold mb-4  ">EQUapp</h1>
                <p class="text-lg opacity-90">Empowering Equity, <span class="text-sky-800">Uniting Communities</span>
                </p>
                <div class="mt-8 space-y-2 text-sm opacity-75">
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div
            class="w-full lg:w-2/3 relative flex items-center justify-center p-8 bg-white shadow-black/20 shadow-[-10px_0px_20px_20px_rgba(255,255,255,0.5)]">
            <div
                class="absolute top-0 left-0 right-0 bg-green-500/60 text-white p-2 text-center flex text-xl justify-center items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span class="font-semibold tracking-wider">Device Management Portal</span>
            </div>
            <div class="max-w-md w-full relative">
                <div class="">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-2 opacity-40 hover:opacity-100 group absolute -top-6 left-0 text-sm text-gray-600 hover:text-gray-800 transition">
                        <i
                            class="fa-solid fa-arrow-left text-lg group-hover:-translate-x-1.5 transition-all duration-500"></i>
                        <span>Module</span>
                    </a>
                </div>
                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 tracking-wide">Welcome to Device Management Portal</h2>
                    {{-- <p class="text-gray-500 mt-2">Please sign in to your account</p> --}}
                </div>

                <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="name@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="••••••••">
                    </div>

                    {{-- <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Forgot password?</a>
                    </div> --}}

                    <button type="submit"
                        class="w-full py-3  bg-blue-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
    @include('partials.sweetalert-toast')
</body>

</html>
