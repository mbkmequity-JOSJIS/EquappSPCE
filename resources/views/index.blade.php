@extends('layouts.app')


@section('title', 'Welcome')

@section('style')
    <style>
        @keyframes slideLeft {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-slide-left {
            animation: slideLeft 30s linear infinite;
        }

        .marquee-container:hover .animate-slide-left {
            animation-play-state: paused;
        }

        /* style untuk maps */
        #footerMap {
            height: 300px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }

        /* Custom marker popup */
        .custom-popup .leaflet-popup-content-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 0;
        }

        .custom-popup .leaflet-popup-content {
            margin: 12px 16px;
        }

        .custom-popup .leaflet-popup-tip {
            background: #764ba2;
        }

        /* Hover effect untuk marker */
        .custom-marker {
            transition: all 0.3s ease;
        }

        .custom-marker:hover {
            filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.3));
            transform: scale(1.1);
        }

        /* Loading map */
        .map-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
    </style>
@endsection

@section('content')
    <section class="pl-10 h-dvh relative overflow-hidden" id="welcome">
        <div class="h-full  gap-10 text-sky-300 z-40 relative">
            <h1 class="text-[10rem] font-bold"><span class="text-[#a9cbe0] text-[12rem]">EQU</span>app</h1>
            <p class="text-xl text-center max-w-2xl text-slate-400">Empowering Equity, Uniting Communities: Your Gateway to a
                More
                Inclusive Future.</p>
        </div>
        <div style="background-image: url('{{ asset('logo.png') }}');" id="bg-logo-equity"
            class="bg-cover bg-center h-[50rem] w-[50rem] absolute grayscale opacity-20 bottom-10 -right-70 z-0">
        </div>
    </section>
    <section class="pl-10 h-dvh bg-[#0ea5e9]/40 shadow-[0_0px_40px_20px_#0ea5e9] shadow-sky-500/50" id="sdgs">
        <div id="sub-header"
            class="sticky top-0 left-0 right-0 z-50 text-white bg-linear-to-r from-transparent to-white flex gap-3 text-xl font-semibold tracking-widest">
            <span>1</span>
            <p>SDGs</p>
        </div>
        <div class="flex items-center justify-center h-full w-full overflow-hidden">
            <img src="{{ asset('images/sdgs.png') }}" alt="SDGs" class="h-[24rem]">
            <div class="ml-10 flex flex-col justify-center items-start gap-6">
                <div class="flex flex-col gap-2">
                    <h1 class="text-6xl font-bold text-white underline underline-offset-8">SDGs</h1>
                    <h2 class="text-2xl font-bold text-slate-600">Sustainable Development Goals</h2>
                </div>
                <p class="text-2xl text-white pr-10">This Equity project leverages Internet of Things to provide real-time
                    environmental monitoring and support data-driven decisions. It contributes to SDG 6 Clean Water and
                    Sanitation and SDG 3 Good Health and Well-being by monitoring water and air quality, while also
                    supporting sustainability efforts aligned with SDG 11 Sustainable Cities and Communities.</p>
            </div>
        </div>
    </section>
    <section class="pt-14" id="equproject">
        <div id="sub-header"
            class="sticky top-0 left-0 mb-5 right-0 z-50 text-white  bg-linear-to-r from-transparent to-sky-200 flex justify-end gap-3 px-10 text-xl font-semibold tracking-widest">
            <span>2</span>
            <p>EQUITY Project</p>
        </div>
        <div class="" id="case-preview">
            <div style="background-image: url('{{ asset('images/polusi.jpg') }}'); "
                class="pl-0 bg-cover bg-[0px_-120px] opacity-85 h-96 w-full flex items-end justify-end text-right p-10">
                <p class="text-3xl text-white font-semibold w-3/5 opacity-100">
                    Environmental monitoring still relies on manual and periodic methods, making the process inefficient and
                    unable to provide real-time insights.
                </p>
            </div>
            <div class="flex items-center justify-between text-left p-10 h-96">
                <p class="text-3xl text-sky-600 font-semibold w-3/5 opacity-100">
                    Traditional monitoring methods often fail to capture critical changes in air and water quality, leading
                    to
                    environmental risks and potential health issues for communities.
                </p>
                <i class="fa-solid fa-circle-radiation text-sky-600 text-[10rem]"></i>
            </div>
            <div style="background-image: url('{{ asset('images/iot.jpg') }}'); "
                class="bg-cover bg-center h-96 w-full flex items-start justify-start p-2 opacity-50 relative">
                <p class="text-xl text-sky-600 font-semibold w-2/5 opacity-100 p-10 text-white">
                    The EQUITY project addresses these challenges by implementing an IoT-based environmental monitoring
                    system
                    that provides real-time data on air and water quality, enabling proactive measures to protect the
                    environment and public health.
                </p>
                {{-- SDGs implementation --}}
                <div class="justify-self-end absolute bottom-0 right-0 [&_span]:font-semibold w-2/5 flex gap-1 text-sm">
                    <div
                        class=" bg-slate-500/30 hover:bg-green-500/50 hover:translate-x-1 hover:-translate-y-2 hover:shadow-2xl  transition-all duration-500 p-4 w-fit h-fit cursor-pointer group rounded-md text-white">
                        <span>SDGs 3 - Good Health and Well-being</span>
                    </div>
                    <div
                        class=" bg-slate-500/30 hover:bg-sky-400/50 hover:translate-x-1 hover:-translate-y-2 hover:shadow-2xl  transition-all duration-500 p-4 w-fit h-fit cursor-pointer group rounded-md mb-4 text-white">
                        <span>SDGs 6 - Clean Water and Sanitation</span>
                    </div>
                    <div
                        class=" bg-slate-500/30 hover:bg-yellow-400/50 hover:translate-x-1 hover:-translate-y-2 hover:shadow-2xl  transition-all duration-500 p-4 w-fit h-fit cursor-pointer group rounded-md text-white">
                        <span>SDGs 11 - Sustainable Cities and Communities</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="pl-10 flex flex-col mt-20 pr-10 h-full" id="equity-goal">
            {{-- Project Goals header --}}
            <h1 class="text-5xl tracking-wider">What is the <span
                    class="text-white font-bold bg-sky-300 rounded-xs px-1.5">EQUITY</span> Project Goals?</h1>
            {{-- Project Goals content --}}
            <p class="text-2xl text-slate-500 tracking-tight leading-relaxed mt-10 w-4/5 [&>span]:font-semibold">
                Through IoT <span class="text-sky-300">AQUAVISKA</span> and IoT <span
                    class="text-orange-300">CLIMATE</span>, this project aims to bridge the gap in environmental awareness
                by
                providing real-time, accessible data on water and air quality. By empowering communities and educational
                environments with reliable information, it ensures that everyon regardless of background has the
                opportunity to make informed decisions, promote healthier living conditions, and contribute to a more
                sustainable and inclusive future.
            </p>
        </div>
        <div class="mt-10 overflow-hidden" id='equity-support'>
            <h2 class="pl-10 text-xl"><span class="font-semibold">Our Support</span> by</h2>
            <div
                class="marquee-container flex items-center bg-slate-300/50 shadow-2xl shadow-slate-600 py-10 mt-5 overflow-hidden">
                <div class="flex justify-evenly items-center gap-20 animate-slide-left whitespace-nowrap">
                    <img src="{{ asset('images/dikris-logo.png') }}" alt="dikristek-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/uns-logo.png') }}" alt="uns-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/ptik-logo.png') }}" alt="ptik-Logo" class="h-20 inline-block">
                    <!-- Duplikat untuk seamless looping -->
                    <img src="{{ asset('images/dikris-logo.png') }}" alt="dikristek-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/uns-logo.png') }}" alt="uns-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/ptik-logo.png') }}" alt="ptik-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/dikris-logo.png') }}" alt="dikristek-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/uns-logo.png') }}" alt="uns-Logo" class="h-20 inline-block">
                    <img src="{{ asset('images/ptik-logo.png') }}" alt="ptik-Logo" class="h-20 inline-block">
                </div>
            </div>
        </div>
    </section>
    <section class="h-dvh pt-14" id="about-us">
        <div id="sub-header"
            class="sticky top-0 left-0 right-0 h-8 z-50 text-white  bg-linear-to-r from-sky-300 to-transparent  flex justify-start items-center gap-3 px-10 text-xl font-semibold tracking-widest">
            <span>3</span>
            <p>About Us</p>
        </div>
        <div class="mt-10">
            <div class="flex justify-evenly items-center" id="mentor-area">
                {{-- card info member --}}
                <div class="w-[20rem] h-[25rem] rounded-3xl shadow-2xl shadow-sky-500/50 relative overflow-hidden">
                    <div class="h-full bg-cover bg-center rounded-t-3xl"
                        style="background-image: url('{{ asset('images/mentor.png') }}');"></div>
                    <div
                        class="absolute bottom-0 left-0 right-0 group h-1/5 hover:h-3/5 transition-all duration-500 bg-slate-500/40 hover:bg-slate-500/80 p-5 flex flex-col justify-center items-start gap-1.5">
                        <h1 class="text-xl font-bold text-white">Our Mentor</h1>
                        <p class="text-sm text-white hidden group-hover:block delay-500">He is an Associate Professor and
                            the Head of the Informatics Education Study Program at Universitas
                            Sebelas Maret (UNS). Earned his Ph.D. from Swinburne University of Technology, Australia,
                            specializing in Information Systems. His research focuses on the integration of technology in
                            education, particularly in the fields of Educational Robotics and Computational Thinking.</p>
                    </div>
                </div>
                {{-- bio --}}
                <div class="flex flex-col gap-2 w-1/2">
                    <a href="https://www.linkedin.com/in/cucuk-budiyanto-97495658/" target="_blank">
                        <h1 class="text-4xl font-semibold ">Mr. <span class="text-sky-500">Cucuk Wawan Budiyanto</span> ST.,
                            PH.D.</h1>
                    </a>
                    <p class="text-shadow-md text-shadow-slate-200 text-gray-400 text-sm">
                        Lecturer in the informatics and computer engineering education study program | Sebelas Maret
                        University</p>
                </div>
            </div>

        </div>
    </section>

    <section class="h-dvh pt-14" id="faq">
        <div id="sub-header"
            class="sticky top-0 left-0 right-0 h-8 z-50 text-white  bg-linear-to-r from-transparent to-green-300  flex justify-end items-center gap-3 px-10 text-xl font-semibold tracking-widest">
            <span>4</span>
            <p>FAQ</p>
        </div>
        {{-- faq content --}}
        <div class="flex p-10 mt-10 max-h-[95%] overflow-hidden" id="content-faq">
            {{-- faq list --}}
            <div class="w-1/4">
                <h1 class="text-xl tracking-wide font-bold text-slate-600">Frequently Asked Questions</h1>
                <p class="text-lg text-slate-500 mt-5">Here are some of our FAQs. If you have any other questions, please
                    feel free to contact us.</p>
            </div>
            {{-- faq items --}}
            <div
                class="w-3/4 flex flex-col gap-5 border-l-2 border-slate-300 pl-5 [&>div]:hover:bg-slate-300/50 [&>div]:hover:translate-x-1 [&>div]:transition-all [&>div]:duration-500 ">
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">What is the EQUITY project? <i
                            class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all  duration-500">The EQUITY
                        project is an initiative that leverages
                        Internet of Things (IoT) technology to provide real-time environmental monitoring. It aims to
                        empower communities and educational environments with accessible data on water and air quality,
                        contributing to a more sustainable and inclusive future.</p>
                </div>
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">How does the EQUITY project
                        contribute to SDGs? <i class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all  duration-500">The EQUITY
                        project contributes to several Sustainable
                        Development Goals (SDGs) by providing real-time data on environmental conditions. It supports SDG 3
                        (Good Health and Well-being) by monitoring air quality, SDG 6 (Clean Water and Sanitation) by
                        tracking water quality, and SDG 11 (Sustainable Cities and Communities) by promoting sustainable
                        living environments.</p>
                </div>
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">Who can benefit from the EQUITY
                        project? <i class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all  duration-500">The EQUITY
                        project is designed to benefit a wide range of
                        stakeholders, including local communities, educational institutions, environmental organizations,
                        and policymakers. By providing accessible environmental data, it empowers individuals and
                        organizations to make informed decisions that promote sustainability and public health.</p>
                </div>
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">How can I get involved with the
                        EQUITY project? <i class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all duration-500">There are
                        several ways to get involved with the EQUITY
                        project. You can participate in our community events, contribute to our research initiatives, or
                        collaborate with us on projects that align with our mission. Please contact us for more information
                        on how to get involved.</p>
                </div>
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">Where can I find more information
                        about the EQUITY project? <i class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all  duration-500">You can find
                        more information about the EQUITY project on
                        our website, where we regularly update our blog with the latest news and developments. Additionally,
                        you can follow us on social media for real-time updates and engagement.</p>
                </div>
                <div class="transition-all duration-500 p-5 rounded-md cursor-pointer">
                    <h2 class="text-xl flex justify-between items-center" id="question">How can I support the EQUITY
                        project? <i class="fa-solid fa-plus transition-all duration-500"></i> </h2>
                    <p class="text-sm text-slate-600 mt-2 h-0 overflow-hidden transition-all  duration-500">You can support
                        the EQUITY project by donating, volunteering, or spreading awareness about our mission. Every
                        contribution helps us make a positive impact on environmental sustainability and community
                        well-being.</p>
                </div>
            </div>
    </section>

    <section class="h-dvh pt-14" id="contact">
        <div id="sub-header"
            class="sticky top-0 left-0 right-0 h-8 z-50 text-white  bg-linear-to-r from-orange-300 to-transparent  flex justify-start items-center gap-3 px-10 text-xl font-semibold tracking-widest">
            <span>5</span>
            <p>Contact Us</p>
        </div>
        <div class="p-10 font-semibold text-slate-600 tracking-wide">
            <p>If you have any questions or would like to get in touch with us, please fill out the form below and we will
                get back to you as soon as possible.</p>
        </div>
        <div class="flex justify-end items-start" id="content-contact">
            <div class="w-1/5 box-border h-full flex items-start justify-start p-10">
                <h1 class="text-3xl font-bold text-slate-500">Get in Touch</h1>
            </div>
            <form action="" class="relative w-4/5 pl-5 border-l-2 border-l-orange-300" method="post">
                @csrf
                <div class="bg-cover bg-center h-96 w-96 absolute top-10 right-100 opacity-65 z-0"
                    style="background-image: url('{{ asset('logo.png') }}');"></div>
                <div class="p-10 flex flex-col gap-6 z-50">
                    <div class="mb-4">
                        <label for="name" class="block text-xl font-medium text-slate-400 tracking-wider">Name</label>
                        <input type="text" id="name" name="name"
                            class="mt-1 block p-3 text-slate-700 w-full h-10 border  backdrop-blur-sm bg-slate-300/30 border-slate-300 rounded-md shadow-sm focus:border-none focus:border focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="email"
                            class="block text-xl font-medium text-slate-400 tracking-wider">Email</label>
                        <input type="email" id="email" name="email"
                            class="mt-1 block p-3 text-slate-700 w-full h-10 border  backdrop-blur-sm bg-slate-300/30 border-slate-300 rounded-md shadow-sm focus:border-none focus:border focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="message"
                            class="block text-xl font-medium text-slate-400 tracking-wider">Message</label>
                        <textarea id="message" name="message" rows="4"
                            class="mt-1 block p-3 text-slate-700 w-full h-20 border  backdrop-blur-sm bg-slate-300/30 border-slate-300 rounded-md shadow-sm focus:border-none focus:border focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <button type="submit"
                        class="w-40 bg-orange-500/50 backdrop-blur-sm hover:bg-orange-500 text-white cursor-pointer font-bold py-2 px-4 rounded">
                        Send Message
                    </button>
                </div>
            </form>

        </div>
    </section>
    <footer class="mt-5 shadow-2xl shadow-slate-600">
        <div class="container mx-auto px-4 py-12 pl-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Informasi Kontak -->
                <div>
                    <h3 class="text-2xl text-slate-500 tracking-wider font-bold mb-6 flex items-center gap-2">
                        Our Location
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-location-dot text-slate-400 mt-1"></i>
                            <div>
                                <p class="font-semibold">Address</p>
                                <p class="text-gray-300 text-sm" id="address-text">
                                    Jl. Ir. Sutami No.36, Kentingan, Kec. Jebres, Kota Surakarta, Jawa Tengah 57126
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <i class="fas fa-phone text-slate-400 mt-1"></i>
                            <div>
                                <p class="font-semibold">Phone</p>
                                <p class="text-gray-300 text-sm">+62 271 646994</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <i class="fas fa-envelope text-slate-400 mt-1"></i>
                            <div>
                                <p class="font-semibold">Email</p>
                                <p class="text-gray-300 text-sm">info@equapp.com</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <i class="fas fa-clock text-slate-400 mt-1"></i>
                            <div>
                                <p class="font-semibold">Office Hours</p>
                                <p class="text-gray-300 text-sm">Monday - Friday: 08:00 - 17:00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="mt-8">
                        <h4 class="font-semibold mb-3 text-slate-500 tracking-wider">Follow Us</h4>
                        <div class="flex gap-4">
                            <a href="#"
                                class="w-10 h-10 rounded-md shadow-2xl shadow-slate-700 hover:translate-x-1 hover:-translate-y-1 flex items-center justify-center hover:bg-blue-500 transition-all duration-500">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#"
                                class="w-10 h-10 rounded-md shadow-2xl shadow-slate-700 hover:translate-x-1 hover:-translate-y-1 flex items-center justify-center hover:bg-blue-400 transition-all duration-500">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#"
                                class="w-10 h-10 rounded-md shadow-2xl shadow-slate-700 hover:translate-x-1 hover:-translate-y-1 flex items-center justify-center hover:bg-pink-600 transition-all duration-500">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#"
                                class="w-10 h-10 rounded-md shadow-2xl shadow-slate-700 hover:translate-x-1 hover:-translate-y-1 flex items-center justify-center hover:bg-blue-700 transition-all duration-500">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#"
                                class="w-10 h-10 rounded-md shadow-2xl shadow-slate-700 hover:translate-x-1 hover:-translate-y-1 flex items-center justify-center hover:bg-green-600 transition-all duration-500">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Maps -->
                <div>
                    <h3 class="text-2xl font-bold mb-6 flex items-center text-slate-500 tracking-wider gap-2">
                        Find Us Here
                    </h3>
                    <div id="footerMap" class="relative"></div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        <i class="fas fa-info-circle"></i> Klik marker untuk melihat detail lokasi
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-slate-300/50 shadow-2xl shadow-slate-600 py-10 flex justify-center items-center gap-3">
            <p class="text-sm text-slate-600">&copy; 2024 EQUITY Project. All rights reserved.</p>
        </div>
    </footer>

@endsection

@section('script')
    <script>
        // Interactive code
        // Initialize GSAP and ScrollTrigger
        gsap.registerPlugin(ScrollTrigger);

        // animate welcome section
        gsap.from(["#welcome h1", "#welcome p"], {
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out",
            stagger: 0.3
        });

        gsap.from("#bg-logo-equity", {
            opacity: 0,
            duration: 3,
            ease: "power2.out",
            delay: 1
        });

        // animate sdgs section
        // image
        gsap.from("#sdgs img", {
            scrollTrigger: {
                trigger: "#sdgs",
                start: "top 80%",
                end: "bottom 20%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            x: -100,
            duration: 1,
            ease: "power2.out",
            delay: 0.5
        });
        // text
        gsap.from("#sdgs div > div", {
            scrollTrigger: {
                trigger: "#sdgs",
                start: "top 80%",
                end: "bottom 20%",
                toggleActions: "play none none reverse"
            },
            opacity: 0,
            x: 100,
            duration: 1,
            ease: "power2.out",
            delay: 0.5,
            stagger: 0.3
        });

        ScrollTrigger.create({
            trigger: "#faq",
            start: "top 50%",
            end: "bottom 10%",
            // markers: true,
            onEnter: () => {
                $("#nav-menu").addClass("bg-green-300 shadow-green-300");
            },


            onLeave: () => {
                $("#nav-menu").removeClass("bg-green-300 shadow-green-300");
            },

            onEnterBack: () => {
                $("#nav-menu").addClass("bg-green-300 shadow-green-300");
            },

            onLeaveBack: () => {
                $("#nav-menu").removeClass("bg-green-300 shadow-green-300");
            },

        });

        ScrollTrigger.create({
            trigger: "#contact",
            start: "top 50%",
            end: "bottom 10%",
            // markers: true,
            onEnter: () => {
                $("#nav-menu").addClass("bg-orange-300 shadow-orange-300");
            },

            onLeave: () => {
                $("#nav-menu").removeClass("bg-orange-300 shadow-orange-300");
            },

            onEnterBack: () => {
                $("#nav-menu").addClass("bg-orange-300 shadow-orange-300");
            },

            onLeaveBack: () => {
                $("#nav-menu").removeClass("bg-orange-300 shadow-orange-300");
            }
        });


        // FAQ toggle
        // FAQ toggle - hanya satu yang terbuka (versi efisien)
        let currentOpenQuestion = null;

        document.querySelectorAll("#question").forEach(function(question) {
            question.addEventListener("click", function() {
                const currentAnswer = this.nextElementSibling;
                const isCurrentlyOpen = currentAnswer.classList.contains("h-20");

                // Jika ada pertanyaan lain yang sedang terbuka, tutup
                if (currentOpenQuestion && currentOpenQuestion !== this) {
                    const prevAnswer = currentOpenQuestion.nextElementSibling;
                    prevAnswer.classList.remove("h-20");
                    prevAnswer.classList.add("h-0", "overflow-hidden");
                    currentOpenQuestion.querySelector("i").classList.remove("fa-solid", "rotate-45");
                    currentOpenQuestion.querySelector("i").classList.add("fa-solid", "rotate-0");
                }

                // Toggle jawaban yang diklik
                if (!isCurrentlyOpen) {
                    currentAnswer.classList.remove("h-0", "overflow-hidden");
                    currentAnswer.classList.add("h-20");
                    this.querySelector("i").classList.remove("fa-solid", "rotate-0");
                    this.querySelector("i").classList.add("fa-solid", "rotate-45");
                    currentOpenQuestion = this;
                } else {
                    // Jika yang diklik sedang terbuka, tutup
                    currentAnswer.classList.remove("h-20");
                    currentAnswer.classList.add("h-0", "overflow-hidden");
                    this.querySelector("i").classList.remove("fa-solid", "rotate-45");
                    this.querySelector("i").classList.add("fa-solid", "rotate-0");
                    currentOpenQuestion = null;
                }
            });
        });

        // maps configuration
        const locationConfig = {
            // Koordinat default (Surakarta/UNS)
            center: [-7.560679, 110.856628], // [latitude, longitude]
            zoom: 15,

            // Multiple lokasi (bisa ditambah)
            locations: [{
                    name: "Universitas Sebelas Maret (UNS)",
                    address: "Jl. Ir. Sutami No.36, Kentingan, Kec. Jebres, Kota Surakarta, Jawa Tengah 57126",
                    coordinates: [-7.560679, 110.856628],
                    phone: "+62 271 646994",
                    email: "info@uns.ac.id",
                    type: "main",
                },
                {
                    name: "PTIK UNS",
                    address: "Gedung PTIK, Kampus UNS, Surakarta",
                    coordinates: [-7.5581312447585605, 110.77443554006352],
                    phone: "+62 271 647123",
                    email: "ptik@uns.ac.id",
                    type: "office",
                }
            ],

            // Warna marker berdasarkan tipe
            markerColors: {
                main: "#3b82f6", // Biru
                office: "#10b981", // Hijau
                default: "#8b5cf6" // Ungu
            }
        };

        // Inisialisasi Map
        let map;
        let markers = [];

        function initMap() {
            // Buat map instance
            map = L.map('footerMap').setView(locationConfig.center, locationConfig.zoom);

            // Tambahkan tile layer (peta dasar)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19,
                minZoom: 3
            }).addTo(map);


            // Tambahkan marker untuk setiap lokasi
            locationConfig.locations.forEach((location, index) => {
                addMarker(location, index);
            });

            // Group markers untuk zoom fit
            if (markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }

            // Tambahkan scale bar
            L.control.scale({
                metric: true,
                imperial: false
            }).addTo(map);
        }

        // Fungsi untuk menambahkan marker
        function addMarker(location, index) {
            // Tentukan warna marker
            const color = locationConfig.markerColors[location.type] || locationConfig.markerColors.default;

            // Buat custom icon
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border: 3px solid white;
            transition: all 0.3s ease;
        ">${location.icon || '📍'}</div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            // Buat marker
            const marker = L.marker(location.coordinates, {
                icon: customIcon
            }).addTo(map);

            // Konten popup
            const popupContent = `
                <div class="min-w-[250px]">
                    <div class="flex items-center gap-2 mb-2 text-center">
                        <h3 class="font-bold text-white">${location.name}</h3>
                    </div>
                    
            `;

            marker.bindPopup(popupContent, {
                className: 'custom-popup',
                maxWidth: 300,
                minWidth: 250
            });

            // Optional: Buka popup untuk lokasi utama
            if (location.type === 'main') {
                marker.openPopup();
            }

            markers.push(marker);
        }

        // Fungsi untuk update lokasi (jika ingin custom dari user input)
        function updateLocation(newCoordinates, newAddress) {
            // Hapus marker lama
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            // Update konfigurasi
            locationConfig.center = newCoordinates;
            locationConfig.locations[0].coordinates = newCoordinates;
            locationConfig.locations[0].address = newAddress;

            // Update text address
            document.getElementById('address-text').innerText = newAddress;

            // Refresh map
            map.setView(newCoordinates, 15);
            addMarker(locationConfig.locations[0], 0);
        }

        // Inisialisasi map saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
        });

        // Handle resize untuk responsive
        window.addEventListener('resize', () => {
            if (map) {
                setTimeout(() => {
                    map.invalidateSize();
                }, 200);
            }
        });
    </script>
@endsection
