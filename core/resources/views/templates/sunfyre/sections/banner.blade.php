@php
    $bannerContent = getContent('banner.content', true);
    $featuredGames = App\Models\Game::active()->where('featured', Status::YES)->get();
@endphp

<section class="banner-section bg-img" data-background-image="{{ getImage('assets/images/frontend/banner/' . @$bannerContent->data_values->background_image, '1920x800') }}">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-lg-6 order-lg-1 order-2">
                <div class="banner-content-slider">
                    <div class="banner-content-slider__inner">
                        @foreach ($featuredGames as $game)
                            <div class="banner-slider-item">
                                <div class="banner-slider-item__wrapper">
                                    <div class="banner-content">
                                        <h1 class="banner-content__title">{{ __($game->name) }}</h1>
                                        <p class="banner-content__desc">@lang('Play') {{ __($game->name) }} @lang('at AddisWin Casino. Start winning today!')</p>
                                        <div class="banner-content__button">
                                            @guest
                                                <a href="{{ route('user.play.game', $game->alias) }}" class="btn btn--gradient">@lang('Play Now')</a>
                                            @else
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="{{ route('user.deposit.index') }}" class="btn btn--base btn--sm"><i class="las la-wallet"></i> @lang('Deposit')</a>
                                                    <a href="{{ route('user.withdraw') }}" class="btn btn-outline--base btn--sm bg-white text-dark"><i class="las la-hand-holding-usd"></i> @lang('Withdraw')</a>
                                                    <a href="{{ route('games') }}" class="btn btn-outline--base btn--sm bg-white text-dark"><i class="las la-gamepad"></i> @lang('Play Games')</a>
                                                    <a href="{{ route('user.home') }}" class="btn btn-outline--base btn--sm bg-white text-dark"><i class="las la-home"></i> @lang('Dashboard')</a>
                                                </div>
                                            @endguest
                                        </div>
                                    </div>
                                    <div class="banner-image">
                                        <img src="{{ getImage(getFilePath('game') . '/' . $game->image, '185x215') }}" alt="{{ __($game->name) }}" style="max-height:215px;width:auto;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="banner-slider-dots"></div>
            </div>
            <div class="col-lg-6 order-lg-2 order-1">
                <div class="banner-thumb">
                    <img src="{{ getImage('assets/images/frontend/banner/' . @$bannerContent->data_values->image, '670x675') }}" alt="@lang('image')">
                </div>
            </div>
        </div>
    </div>
</section>
