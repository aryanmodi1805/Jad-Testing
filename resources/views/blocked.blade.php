<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Blocked</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg text-center">
    <h1 class="text-3xl font-bold text-red-600 mb-4">Account Blocked</h1>
    <p class="text-gray-700 mb-6">Your account has been blocked. Please contact support for further assistance.</p>
    <a href="{{ url('/') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Go to Homepage</a>
</div>
</body>
</html>
