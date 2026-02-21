<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .rating {
            display: inline-block;
            position: relative;
            font-size: 1.25rem;
            color: #FFD700;
        }
        .rating::before {
            content: '★★★★★';
            opacity: 0.3;
        }
        .rating::after {
            content: '★★★★★';
            position: absolute;
            top: 0;
            left: 0;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="container mx-auto p-6">
    <!-- Profile Header -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="bg-purple-600 p-6">
            <div class="flex items-center">
                <x-image class="w-24 h-24 rounded-full border-4 border-white mr-6" src="https://via.placeholder.com/100" alt="Profile Picture"/>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $seller->name }}</h1>
                    <div class="flex items-center mt-2 text-white">
                        <svg class="w-5 h-5 text-gray-300 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-1.19 2.004-1.19 2.305 0A1 1 0 0012.3 3.6a1 1 0 00.471-1.33C11.628.444 10.033 0 8.979 0c-1.053 0-2.649.444-3.792 2.27A1 1 0 005.658 4.6a1 1 0 00.472-1.33zM5.007 5.303A1 1 0 013.3 5.6c-1.2.44-1.2 2.144 0 2.583a1 1 0 011.33.471 1 1 0 00-1.307 1.339 1 1 0 01-.628.33C.569 10.64 0 11.633 0 12.643c0 1.025.586 1.97 1.383 2.358a1 1 0 01-.22 1.92C.162 16.793 0 17.368 0 18a1 1 0 001 1h2c0-1.92.68-2.95 1.316-3.696A1 1 0 005 14.6a1 1 0 00.471-1.33 1 1 0 01.33-.627c1.103-.75 2.648-.74 3.853.03a1 1 0 001.538-1.298z"></path></svg>
                        <span class="text-sm">{{ $seller->distance }} miles away</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <span class="text-yellow-500 text-xl">★★★★☆</span>
                        <span class="text-sm ml-2 text-white">(2 reviews)</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap mt-4">
                <div class="flex flex-wrap">
                    @foreach($seller->services as $service)
                        <span class="bg-gray-200 text-gray-800 text-sm font-semibold mr-2 mb-2 px-3 py-1 rounded-full">
                            {{ $service['name'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- Profile Content -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden p-6">
        <nav class="flex space-x-4 mb-6 border-b-2 pb-2">
            <a href="#about" class="text-purple-600 font-semibold hover:underline">About</a>
            <a href="#photos" class="text-purple-600 font-semibold hover:underline">Photos</a>
            <a href="#reviews" class="text-purple-600 font-semibold hover:underline">Reviews</a>
            <a href="#services" class="text-purple-600 font-semibold hover:underline">Services</a>
            <a href="#qa" class="text-purple-600 font-semibold hover:underline">Q&A</a>
        </nav>
        <section id="about" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">About</h2>
            <p>We are always ready to attend to our clients' needs and always provide a response to emails and calls at any time.</p>
            <div class="mt-4">
                <h3 class="text-xl font-semibold">Overview</h3>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M8 9a3 3 0 116 0 3 3 0 01-6 0zM4 4a2 2 0 100 4 2 2 0 000-4zM10 11a2 2 0 100 4 2 2 0 000-4zM2 11a2 2 0 100 4 2 2 0 000-4zM4 15a3 3 0 106 0 3 3 0 00-6 0zM14 15a3 3 0 106 0 3 3 0 00-6 0z"></path></svg>
                        <span>2 hires on Evantto</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884l.343-1.714 14.557 2.915-.344 1.714L2.003 5.884zM2 14.372V12.43a1 1 0 011-1h14a1 1 0 011 1v1.942l-8 2.666-8-2.666z"></path></svg>
                        <span>5 years in business</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M8 4a2 2 0 110 4 2 2 0 010-4zm-3 2a3 3 0 106 0 3 3 0 00-6 0zM10 8h1.586l-2.707 2.707 1.414 1.414L13 9.414V11a1 1 0 102 0V8a1 1 0 00-1-1h-4V5a1 1 0 00-2 0v3zm0 2a1 1 0 00-1 1v2a1 1 0 002 0v-2a1 1 0 00-1-1z"></path></svg>
                        <span>8 min response time</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 4.383l5.199.774a1 1 0 00.8-.374l1.26-1.571a1 1 0 011.476 0l1.26 1.571a1 1 0 00.8.374l5.199-.774a1 1 0 01.894 1.316l-1.013 3.674a1 1 0 00.287.939L18 12.618v4.384H2v-4.384l1.619-2.306a1 1 0 00.287-.939L2.893 5.699a1 1 0 01.11-.316z"></path></svg>
                        <span>11-50 staff</span>
                    </div>
                </div>
            </div>
        </section>
        <section id="photos" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Photos</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <x-image src="https://via.placeholder.com/150" class="w-full h-40 object-cover rounded-lg shadow-md" alt="Photo"/>
                <x-image src="https://via.placeholder.com/150" class="w-full h-40 object-cover rounded-lg shadow-md" alt="Photo"/>
                <x-image src="https://via.placeholder.com/150" class="w-full h-40 object-cover rounded-lg shadow-md" alt="Photo"/>
                <x-image src="https://via.placeholder.com/150" class="w-full h-40 object-cover rounded-lg shadow-md" alt="Photo"/>
            </div>
        </section>
        <section id="reviews" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Reviews</h2>
            <div class="flex items-center mb-4">
                <span class="text-yellow-500 text-2xl">★★★★☆</span>
                <span class="text-lg ml-2">4.5/5</span>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-500">★★★★★</span>
                        <span class="text-sm ml-2">by John Doe</span>
                    </div>
                    <p>Excellent service and high-quality work!</p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                    <div class="flex items-center mb-2">
                        <span class="text-yellow-500">★★★★☆</span>
                        <span class="text-sm ml-2">by Jane Smith</span>
                    </div>
                    <p>Very satisfied with the results. Will hire again!</p>
                </div>
            </div>
        </section>
        <section id="services" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Services</h2>
            <div class="flex flex-wrap">
                @foreach($seller->services as $service)
                    <span class="bg-gray-200 text-gray-800 text-sm font-semibold mr-2 mb-2 px-3 py-1 rounded-full">
                            {{ $service['name'] }}
                        </span>
                @endforeach
            </div>
        </section>
        <section id="qa" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Q&As</h2>
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <button class="flex justify-between items-center w-full focus:outline-none" onclick="toggleQA('qa1')">
                        <span class="font-semibold">What do you love most about your job?</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="qa1" class="mt-2 hidden">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris consequat lorem ut turpis tincidunt, ac bibendum nisi fermentum.</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <button class="flex justify-between items-center w-full focus:outline-none" onclick="toggleQA('qa2')">
                        <span class="font-semibold">What inspired you to start your own business?</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="qa2" class="mt-2 hidden">
                        <p>The ability to work for myself and maintaining a consistent good service to our clients.</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <button class="flex justify-between items-center w-full focus:outline-none" onclick="toggleQA('qa3')">
                        <span class="font-semibold">Why should our clients choose you?</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="qa3" class="mt-2 hidden">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris consequat lorem ut turpis tincidunt, ac bibendum nisi fermentum.</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <button class="flex justify-between items-center w-full focus:outline-none" onclick="toggleQA('qa4')">
                        <span class="font-semibold">What changes have you made to keep your customers safe from Covid-19?</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="qa4" class="mt-2 hidden">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris consequat lorem ut turpis tincidunt, ac bibendum nisi fermentum.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    function toggleQA(id) {
        const element = document.getElementById(id);
        element.classList.toggle('hidden');
    }
</script>

</body>
</html>
