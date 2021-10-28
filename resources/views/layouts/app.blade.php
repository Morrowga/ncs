<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ asset('web/assets/favicon.ico') }}">
    <!-- CSRF Token -->

    {{-- <title>{{ $title }} | {{ config('app.name', 'Laravel') }}</title> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Styles -->
    {{--
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{ asset('web/css/simplebar.css') }}">
    <!-- Fonts CSS -->
    <link
        href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{ asset('web/css/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/dropzone.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/uppy.min.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/jquery.steps.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ asset('web/css/summernote-bs4.min.css') }}">
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="{{ asset('web/css/daterangepicker.css') }}">
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('web/css/app-light.css') }}" id="lightTheme">

    {{-- excel --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4/css/bootstrap.min.css" />

    {{--
    <link rel="stylesheet" href="{{ asset('web/css/app-dark.css') }}" id="darkTheme" disabled> --}}
    {{-- jquery --}}
    {{--
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css"> --}}
    <style>
        body {
            font-size: 0.9375rem;
        }

        .div-page ul {
            justify-content: center !important;
        }

        .form-control {
            font-size: 0.9375rem;
            border-color: #b5b5b5;
        }

        .div-page ul {
            justify-content: center !important;
        }

        /* select 2 */
        /* .select2-container--default .select2-selection--single, */
        .select2-container--bootstrap4 .select2-selection {
            border-radius: 0;
            border-color: #b5b5b5;
        }

        .select2-container .select2-selection--single {
            height: 35px;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 33px;
        }

        .increase-size {
            font-size: 1rem;
        }

        p.blacklist {
            color: red;
        }

        p.suggest_tag {
            color: blue;
        }

        p.suggest_category {
            color: green;
        }

        /* responsive for mobile */
        @media (max-width: 700px) {

            hr {
                margin-top: 40px;
            }

            div.button {
                margin-top: 15px !important;
                width: 100% !important;
                /* background-color: red; */
                position: absolute;
                display: flex;
                padding-left: 0px !important;
            }

            .button .cancel {
                width: 50px !important;
                margin-left: -100px;
                display: inline-block;
                padding: 8px;
                /* position: relative; */

            }

            .button .edit {
                width: 50px !important;
                margin-left: -10px;
                display: inline-block;
                padding: 8px;
                /* position: relative; */
            }

            .button .send {
                width: 50px !important;
                margin-left: -12px;
                display: inline-block;
                padding: 8px;
                /* position: relative; */
            }

            .button .duplicate {
                width: 70px !important;
                margin-left: 40px;
                display: inline-block;
                padding: 8px;
                /* position: relative; */
            }

            .button .blacklist {
                width: 70px !important;
                margin-left: 40px;
                display: inline-block;
                padding: 8px;
                /* position: relative; */
            }


        }
    </style>

</head>

<body class="vertical light">
    <div class="wrapper">
        <nav class="topnav navbar navbar-light">
            <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
                <i class="fe fe-menu navbar-toggler-icon"></i>
            </button>
            <form class="form-inline mr-auto searchform text-muted">
                <input class="form-control mr-sm-2 bg-transparent border-0 pl-4 text-muted" type="search"
                    placeholder="Type something..." aria-label="Search">
            </form>
            <ul class="nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink"
                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar avatar-sm mt-2">
                            <img src="{{ asset('web/assets/user-icon.jpg') }}" alt="..."
                                class="avatar-img rounded-circle">
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="" onclick="return false;">Profile</a>
                        <a class="dropdown-item" href="" onclick="return false;">Settings</a>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
            <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
                <i class="fe fe-x"><span class="sr-only"></span></i>
            </a>
            <nav class="vertnav navbar navbar-light">
                <!-- nav bar -->
                <div class="w-100 mb-4 d-flex">
                    <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ url('/') }}">
                        <img src="{{ asset('web/assets/favicon_io1/favicon-32x32.png') }}" alt="">
                    </a>
                </div>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ url('/') }}">
                            <i class="fe fe-home fe-16"></i>
                            <span class="ml-3 item-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Articles</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('raw_articles.index') }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Raw Articles') }}</span>
                            <span class="badge badge-pill badge-primary increase-size  pt-1">{{ $count_raw_article
                                }}</span>



                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('sent_articles.index') }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ __('LoTaYa Articles') }}</span>
                            <span class="badge badge-pill badge-success increase-size text-white  pt-1">{{
                                $count_sent_lotaya }}</span>



                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('reject_articles.index') }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Reject Articles') }}</span>
                            <span class="badge badge-pill badge-danger increase-size  pt-1">{{ $count_reject_article
                                }}</span>
                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('laravellogs') }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Server Logs') }}</span>
                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('activity') }}">
                            <i class="fe fe-layers fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Activity Logs') }}</span>
                        </a>
                    </li>
                </ul>

                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Web Scraping</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('website.index') }}">
                            <i class="fe fe-list fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Websites') }}</span>
                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('links.index') }}">
                            <i class="fe fe-calendar fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Scraping Links') }}</span>
                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('itemschema.index') }}">
                            <i class="fe fe-calendar fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Item Schema') }}</span>
                        </a>
                    </li>
                </ul>

                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Settings</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('category.index') }}">
                            <i class="fe fe-list fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Categories') }}</span>
                        </a>
                    </li>
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('tag.index') }}">
                            <i class="fe fe-list fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Tags') }}</span>
                        </a>
                    </li>

                </ul>
                <p class="text-muted nav-heading mt-4 mb-1">
                    <span>Report</span>
                </p>
                <ul class="navbar-nav flex-fill w-100 mb-2">
                    <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('monthly') }}">
                            <i class="fe fe-list fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Montly') }}</span>
                        </a>
                    </li>
                    {{-- <li class="nav-item w-100">
                        <a class="nav-link" href="{{ route('keyword.index') }}">
                            <i class="fe fe-list fe-16"></i>
                            <span class="ml-3 item-text">{{ __('Keywords') }}</span>
                        </a>
                    </li> --}}
                </ul>
            </nav>
        </aside>
    </div>

    <main role="main" class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                {{-- {{ date('Y-m-d', '1634107898') }} --}}
                {{-- {{dd(
                tounicode('၂၀၂၀-၂၀၂၁ ဘ႑ာႏွစ္အတြင္း ေက်ာက္မ်က္ ရတနာႏွင့္ အထည္ခ်ဳပ္ပစၥည္းမ်ား တင္ပို႔ႏိုင္ျခင္း မရွိဘဲ
                ထိခိုက္မႈအမ်ားဆုံး
                ျဖစ္ေသာ္လည္း လယ္ယာထြက္ကုန္မ်ားကို အဓိကထား ေစ်းကြက္ရရွိေအာင္ ပိုမိုေဆာင္ရြက္ေနေၾကာင္း စီးပြားေရးႏွင့္
                ကူးသန္း ေရာင္း၀ယ္ေရး၀န္ႀကီးဌာန၊ ျမန္မာကုန္သြယ္မႈျမႇင့္တင္ေရးအဖြဲ႕ထံမွ သိရသည္။ ကိုဗစ္ဗိုင္းရပ္စ္
                စတင္ကူးစက္ခ်ိန္
                ကစ၍ ေက်ာက္မ်က္ရတနာျပပြဲမ်ား ျပဳလုပ္ႏိိုင္ျခင္းမရွိခဲ့ဘဲ ႏိိုင္ငံတကာ၏ ၀ယ္လိုအားလည္း မရွိခဲ့ေၾကာင္း
                သိရသည္။
                “ေက်ာက္မ်က္နဲ႔ အထည္ခ်ဳပ္က႑ကေတာ့ ထိခိုက္မႈအမ်ားဆုံးလို႔ ေျပာရမွာေပါ့။ လုံး၀ရပ္သြားတာ
                တစ္ႏွစ္ရွိၿပီ။ဒါေၾကာင့္
                ပို႔ကုန္က႑မွာ လယ္ယာထြက္ကုန္ကိုသာ အဓိကအားစိုက္ၿပီး ေစ်းကြက္ရရွိေအာင္ ေဆာင္ရြက္ရမဲ့သေဘာ ရွိပါတယ္”ဟု
                ျမန္မာကုန္သြယ္မႈ ျမႇင့္တင္ေရးအဖြဲ႕ ဒုတိယၫႊန္ၾကားေရးမႉးခ်ဳပ္ ဦးမ်ဳိးသူက ျမန္မာေတာင္သူႀကီးမ်ားဂ်ာနယ္ကို
                ေျပာသည္။
                ေက်ာက္မ်က္ႏွင့္ ေက်ာက္စိမ္းကိုႏိိုင္ငံတကာသို႔ တင္ပို႔ေရာင္းခ်ႏိိုင္ျခင္း မရွိသည့္အျပင္ အဓိက၀ယ္လက္ျဖစ္ေသာ
                တ႐ုတ္ႏိုင္ငံကလည္း ၀ယ္လိုအားမ်ားစြာက်ဆင္းခဲ့ေၾကာင္း သိရသည္။ ျမန္မာအထည္ခ်ဳပ္က႑တြင္ EU ႏိုင္ငံမ်ားကေပး
                ေသာ အေကာက္ခြန္ကင္းလြတ္ခြင့္မ်ား ရရွိထားသျဖင့္ ကိုဗစ္ကာလမတိုင္ခင္က အလားအလာေကာင္းမ်ား ျဖစ္ေပၚခဲ့ေသာ္
                လည္း ကိုဗစ္ - ၁၉ စတင္ကူးစက္လာခ်ိန္မွစ၍ ျမန္မာအထည္ခ်ဳပ္က႑တြင္ ထိခိုက္မႈမ်ားခဲ့ေၾကာင္း သိရသည္။

                ကိုဗစ္ - ၁၉ ေၾကာင့္ အလုပ္လက္မဲ့ျဖစ္ခဲ့ရေသာ အထည္ခ်ဳပ္အလုပ္သမား ၁၃ç၀၀၀ နီးပါးအတြက္ ဥေရာပသမဂၢအေရးေပၚ
                ရန္ပုံေငြျဖစ္သည့္ ျမန္ကူ (Myan Ku) ကတစ္ဆင့္ ယူ႐ိုငါးသန္း (ျမန္မာက်ပ္ေငြ ၇ ဒသမ ၉ ဘီလ်ံ) ကို
                ကူညီေထာက္ပံ့ေပး
                ခဲ့ေၾကာင္း သိရသည္။ အထည္ခ်ဳပ္လုပ္ငန္းက႑တြင္ ႏိုင္ငံတကာ၀ယ္လက္မ်ားထံမွ အ၀ယ္အမွာစာမ်ား ပယ္ဖ်က္ျခင္း၊
                ၾကန႔္ၾကာျခင္း၊ ေလ်ာ့နည္းျခင္းမ်ား ရွိခဲ့သည့္အျပင္ စက္႐ုံအမ်ားစု ပိတ္သိမ္းရသည္မ်ားလည္းရွိေၾကာင္း သိရသည္။
                EU
                ႏိုင္ငံမ်ားသို႔ ဖရဲ၊ သခြား၊ေထာပတ္သီးမ်ား တင္ပို႔မႈမွာလည္း အေအးခန္း ကြန္တိန္နာအခက္အခဲေၾကာင့္
                ေစာင့္ဆိုင္းေနရာမွ
                လတ္တေလာ ႏိုင္ငံေရး အေျပာင္းအလဲမ်ားေၾကာင့္ ရပ္ဆိုင္းသြားေၾကာင္းႏွင့္ ပို႔ကုန္က႑ သေဘာတူညီမႈမ်ားလည္း မေရရာ
                သည့္အေျခအေန ျဖစ္ေနေၾကာင္း သီးႏံွပို႔ကုန္သမားမ်ားထံမွ သိရသည္။

                - ေရႊမန္း

                ျမန္မာေတာင္သူႀကီးမ်ား ဂ်ာနယ္

                အတြဲ(၂၉) အမွတ္(၄၃၅)၊ ေဖေဖာ္၀ါရီ ၂၂ - ၂၈၊ ၂၀၂၁ ခုႏွစ္

                ')
                )}} --}}
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Delete Modal -->
    <div class="modal fade" id="delete-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body delete-modal text-center py-5">
                    <h4>Are you sure to delete?</h4>
                    <br>
                    <form method="POST">
                        @csrf
                        @method("DELETE")
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i
                                class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Close') }}</button>
                        <button type="submit" class="btn btn-outline-danger"><i class="fe fe-trash-2 fe-16 mr-2"></i>
                            {{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Send Modal -->
    <div class="modal fade" id="send-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body send-modal text-center py-5">
                    <h4>Are you sure to send?</h4>
                    <br>
                    <form method="POST">
                        @csrf
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i
                                class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Close') }}</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fe fe-send fe-16 mr-2"></i>
                            {{ __('Send') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Duplicate Modal -->
    <div class="modal fade" id="duplicate-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body duplicate-modal text-center py-5">
                    <h4>Are you sure to duplicate?</h4>
                    <br>
                    <form method="POST">
                        @csrf
                        @method("PUT")
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i
                                class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Close') }}</button>
                        <button type="submit" class="btn btn-outline-warning"><i
                                class="fe fe-minus-circle fe-16 mr-2"></i>
                            {{ __('Duplicate') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Blacklist Modal -->
    <div class="modal fade" id="blacklist-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body blacklist-modal text-center py-5">
                    <h4>Are you sure to blacklist?</h4>
                    <br>
                    <form method="POST">
                        @csrf
                        @method("PUT")
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i
                                class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Close') }}</button>
                        <button type="submit" class="btn btn-outline-danger"><i class="fe fe-trash-2 fe-16 mr-2"></i>
                            {{ __('Blacklist') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('web/js/jquery.min.js') }}"></script>
    <script src='{{ asset(' web/js/jquery.stickOnScroll.js') }}'></script>
    <script src="{{ asset('web/js/popper.min.js') }}"></script>
    <script src="{{ asset('web/js/moment.min.js') }}"></script>
    <script src="{{ asset('web/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('web/js/select2.min.js') }}"></script>
    <script src="{{ asset('web/js/simplebar.min.js') }}"></script>
    <script src='{{ asset(' web/js/daterangepicker.js') }}'></script>
    <script src="{{ asset('web/js/tinycolor-min.js') }}"></script>
    <script src="{{ asset('web/js/config.js') }}"></script>
    <script src="{{ asset('web/js/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('web/js/apps.js') }}"></script>
    <script src="{{ asset('js/myjs.js') }}"></script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-56159088-1"></script>
    {{-- jquery --}}

    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script> --}}

    <script>
        $('.s2').select2({
                theme: 'bootstrap4'
            });
            $('.js-example-basic-single').select2({
                theme: 'bootstrap4',

            });
            $('.s2s').select2({
                theme: 'bootstrap4',
                placeholder: {
                    text: 'Choose Tags ..'
                }
            });
            $('.summernote').summernote({
                height: 350,
                toolbar: [
                    // [groupName, [list of button]]
                    // ['style', ['bold', 'italic', 'underline', 'clear']],
                    // ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    // ['para', ['ul', 'ol', 'paragraph']]
                ]
            });

            $('.date-picker').daterangepicker( {
                singleDatePicker: true,
                timePicker: true,
                timePickerSeconds: true,
                timePicker24Hour: true,
                autoApply: true,
                locale: {
                    format: 'YYYY-MM-DD hh:mm:ss A'
                }
            } );
            // window.dataLayer = window.dataLayer || [];

            // function gtag()
            // {
            //     dataLayer.push(arguments);
            // }
            // gtag('js', new Date());
            // gtag('config', 'UA-56159088-1');

    </script>

    <script type="text/javascript">
        $(function () {
            $("select.item_schema").on('change', function () {
                if($(this).val() != $(this).attr("data-original-schema")) {
                    $(this).siblings('.btn-apply').show();
                }
            });

            $('.btn-apply').on('click', function () {
                var link_id = $(this).parents("tr").attr("data-id");
                var schema_id = $(this).siblings('select').val();
                //    var token = $('input[name=_t.elementor-container .content-inneroken]').val();

                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                });

                // console.log(link_id, schema_id)

            //    $.patch(window.location.origin + "/links/set_item_schema", {link_id, schema_id}, function(res) {
            //        console.log(res)
            //    }, 'json')

                $.ajax({
                    url: window.location.origin + "/links/set-item-schema",
                    data: {link_id, schema_id },
                    method: "POST",
                    dataType: "json",
                    success: function (response) {
                        // console.log(response.msg);
                        if (response.status) {
                            window.location.reload();
                            btn.hide();
                            $('.btn-scrape').removeAttr('disabled')
                        }
                        console.log(error.reponse); // logs an object to the console
                    }
                });
            });

            setInterval(() => {
                $(".btn-scrape").on('click', function () {
                    var btn = $(this);
                    btn.find(".fast-right-spinner").show();
                    btn.prop("disabled", true);
                    var tRowId = $(this).parents("tr").attr("data-id");

                    console.log(tRowId)

                    $.ajaxSetup({
                        headers: {
                            'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: window.location.origin + "/links/scrape",
                        data: {link_id: tRowId, _token: $('meta[name="csrf-token"]').attr('content')},
                        method: "post",
                        dataType: "json",
                        success: function (response) {

                            if(response.status == 1) {
                                // $(".alert").removeClass("alert-danger").addClass("alert-success").text(response.msg).show();
                                console.log(response.msg)
                            } else {
                                // $(".alert").removeClass("alert-success").addClass("alert-danger").text(response.msg).show();
                                console.log(response.msg)
                            }

                            btn.find(".fast-right-spinner").hide();
                        }
                    });
                });
            }, 2000);
        });
    </script>
</body>

</html>
