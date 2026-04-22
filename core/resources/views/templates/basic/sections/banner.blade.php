@php
    $banner = getContent('banner.content', true);
    $featuredGames = App\Models\Game::active()->where('featured', Status::YES)->get();
@endphp
<section class="hero bg_img" style="background-image: url( {{ getImage('assets/images/frontend/banner/' . @$banner->data_values->image, '1920x780') }} );">
    <div class="container">
        <div class="hero-slider">
            @foreach($featuredGames as $game)
                <div class="single-slide">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-lg-6">
                            <div class="hero__content text-lg-left">
                                <h2 class="hero__title wow fadeInUp" data-wow-duration="0.5s" data-wow-delay="0.3s">{{ __($game->name) }}</h2>
                                <p class="wow fadeInUp" data-wow-duration="0.5s" data-wow-delay="0.5s">@lang('Play') {{ __($game->name) }} @lang('at AddisWin Casino. Start winning today!')</p>
                                @guest
                                    <div class="btn-group justify-content-lg-start justify-content-center wow fadeInUp mt-4" data-wow-duration="0.5s" data-wow-delay="0.9s">
                                        <a class="cmn-btn" href="{{ route('user.play.game', $game->alias) }}">@lang('Play Now')</a>
                                    </div>
                                @else
                                    <div class="d-flex flex-wrap gap-2 justify-content-lg-start justify-content-center wow fadeInUp mt-4 quick-actions" data-wow-duration="0.5s" data-wow-delay="0.9s">
                                        <a href="{{ route('user.deposit.index') }}" class="cmn-btn btn-sm"><i class="las la-wallet"></i> @lang('Deposit')</a>
                                        <a href="{{ route('user.withdraw') }}" class="cmn-btn-two btn-sm"><i class="las la-hand-holding-usd"></i> @lang('Withdraw')</a>
                                        <a href="{{ route('games') }}" class="cmn-btn-two btn-sm"><i class="las la-gamepad"></i> @lang('Play Games')</a>
                                        <a href="{{ route('user.home') }}" class="cmn-btn-two btn-sm"><i class="las la-home"></i> @lang('Dashboard')</a>
                                    </div>
                                @endguest
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6 d-none d-md-block">
                            <div class="hero__thumb wow fadeInUp" data-wow-duration="0.5s" data-wow-delay="0.3s">
                                <img src="{{ getImage(getFilePath('game') . '/' . $game->image, getFileSize('game')) }}" alt="{{ __($game->name) }}" style="max-height:350px;width:auto;object-fit:contain;">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('style')
<style>
    @media (max-width: 991px) {
        .hero.bg_img {
            background-image: none !important;
        }
    }
</style>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        $('.hero-slider').slick({
            dots: false,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            arrows: false
        });
    })(jQuery);
</script>
@endpush
