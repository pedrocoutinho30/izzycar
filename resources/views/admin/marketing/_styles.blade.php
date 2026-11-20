{{-- Estilos dos templates do Marketing Studio. Tokens = identidade Izzycar (bordeaux, dourado, preto, Inter). --}}
<style>
@font-face{font-family:'Inter';font-weight:400;font-style:normal;font-display:block;src:url('{{ asset('fonts/inter/inter-400.woff2') }}') format('woff2')}
@font-face{font-family:'Inter';font-weight:600;font-style:normal;font-display:block;src:url('{{ asset('fonts/inter/inter-600.woff2') }}') format('woff2')}
@font-face{font-family:'Inter';font-weight:700;font-style:normal;font-display:block;src:url('{{ asset('fonts/inter/inter-700.woff2') }}') format('woff2')}

.mk-slide{--ink:#111111;--wine:#6e0707;--wine-deep:#4a0505;--gold:#B59151;--gold-deep:#92642D;--cream:#f6f1e8;
  position:relative;overflow:hidden;display:flex;flex-direction:column;box-sizing:border-box;
  font-family:'Inter',Arial,sans-serif;color:#fff;line-height:1.15;text-align:left}
.mk-slide *{box-sizing:border-box;margin:0;padding:0}
.mk-photo{background-size:cover;background-repeat:no-repeat;background-color:#262626;min-height:0}
.mk-badge-logo{position:absolute;top:56px;right:56px;background:rgba(17,17,17,.86);padding:20px 26px;border-radius:6px}
.mk-badge-logo img{display:block;height:120px;width:auto}
.mk-logo{display:block;width:auto}
.mk-title{font-weight:700;letter-spacing:-.02em;line-height:1.02}
.mk-title.t-lg{font-size:96px}.mk-title.t-md{font-size:78px}.mk-title.t-sm{font-size:62px}
.mk-sub{font-size:36px;font-weight:400;margin-top:16px;opacity:.82}
.mk-eyebrow{font-size:26px;font-weight:600;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);margin-bottom:22px}
.mk-rule{height:3px;width:120px;background:var(--gold);margin:30px 0}
.mk-price{font-weight:700;letter-spacing:-.02em}
.mk-ribbon{position:absolute;top:56px;left:0;padding:22px 44px 22px 72px;font-size:32px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.mk-list{list-style:none}
.mk-list li{position:relative;padding-left:42px;font-size:36px;font-weight:600;line-height:1.2;margin-bottom:22px}
.mk-list li::before{content:'';position:absolute;left:0;top:.38em;width:18px;height:18px;background:var(--gold)}

/* Premium */
.mk-premium{background:var(--ink)}
.mk-premium__photo{flex:1 1 auto;position:relative}
.mk-premium__body{padding:54px 72px 60px;display:flex;flex-direction:column}
.mk-premium__foot{display:flex;justify-content:space-between;align-items:flex-end;margin-top:6px}
.mk-premium .mk-price{font-size:72px;color:var(--gold)}

/* Comercial */
.mk-comercial{background:#fff;color:var(--ink)}
.mk-comercial__photo{flex:1 1 auto;position:relative}
.mk-comercial__bg{position:absolute;left:0;top:0;right:0;bottom:0}
.mk-comercial__tag{position:absolute;left:0;bottom:0;background:var(--wine);color:#fff;padding:26px 64px 28px 72px}
.mk-comercial__tag small{display:block;font-size:24px;letter-spacing:.2em;text-transform:uppercase;opacity:.8;margin-bottom:8px}
.mk-comercial__tag b{font-size:84px;font-weight:700;letter-spacing:-.02em}
.mk-comercial__body{padding:48px 72px 40px}
.mk-comercial .mk-title{color:var(--ink)}.mk-comercial .mk-title.t-lg{font-size:80px}.mk-comercial .mk-title.t-md{font-size:68px}.mk-comercial .mk-title.t-sm{font-size:56px}
.mk-comercial .mk-sub{color:#555;opacity:1;font-size:32px}
.mk-comercial .mk-list{margin-top:34px;display:flex;flex-wrap:wrap}
.mk-comercial .mk-list li{width:50%;font-size:32px;padding-right:20px;margin-bottom:16px}
.mk-comercial__cta{background:var(--ink);display:flex;justify-content:space-between;align-items:center;padding:34px 72px;font-size:36px;font-weight:600}
.mk-comercial__cta span:last-child{color:var(--gold);font-weight:700}

/* Minimalista */
.mk-minimal{background:var(--cream);color:var(--ink);padding:56px}
.mk-minimal__logo{height:110px;margin:0 auto 34px}
.mk-minimal__photo{flex:1 1 auto;border-radius:22px;overflow:hidden}
.mk-minimal__foot{display:flex;justify-content:space-between;align-items:flex-end;gap:32px;padding-top:40px}
.mk-minimal__foot>div:first-child{flex:1 1 auto;min-width:0}
.mk-minimal .mk-title{font-size:64px;color:var(--ink)}.mk-minimal .mk-title.t-md{font-size:54px}.mk-minimal .mk-title.t-sm{font-size:44px}
.mk-minimal .mk-sub{font-size:30px;color:#6b6258;opacity:1}
.mk-minimal .mk-price{font-size:60px;color:var(--gold-deep);white-space:nowrap;flex:0 0 auto}

/* Equipamento */
.mk-equip{background:#141414}
.mk-equip__photo{flex:0 0 44%;position:relative}
.mk-equip__body{flex:1 1 auto;padding:56px 72px 50px;display:flex;flex-direction:column}
.mk-equip .mk-title{font-size:70px}.mk-equip .mk-title.t-sm{font-size:56px}
.mk-equip .mk-list{margin-top:40px;column-count:2;column-gap:40px}
.mk-equip .mk-list li{font-size:34px;break-inside:avoid}
.mk-equip__foot{margin-top:auto;display:flex;justify-content:space-between;align-items:center;border-top:2px solid rgba(181,145,81,.5);padding-top:30px;font-size:30px;font-weight:600}
.mk-equip__foot img{height:96px}

/* Novo em stock / Vendido (foto total) */
.mk-full{background:var(--ink)}
.mk-full__photo{position:absolute;left:0;top:0;right:0;bottom:0}
.mk-full__scrim{position:absolute;left:0;right:0;bottom:0;height:52%;background:linear-gradient(to bottom,rgba(17,17,17,0),rgba(17,17,17,.92))}
.mk-full__body{position:absolute;left:72px;right:72px;bottom:64px}
.mk-full .mk-price{font-size:78px;color:var(--gold);margin-top:26px}
.mk-full .mk-ribbon.gold{background:var(--gold);color:var(--ink)}
.mk-full .mk-ribbon.wine{background:var(--wine);color:#fff}
.mk-full__meta{font-size:28px;font-weight:600;letter-spacing:.06em;color:var(--gold);margin-top:26px}

/* Recomendação */
.mk-reco{background:var(--ink)}
.mk-reco__photo{flex:0 0 50%;position:relative}
.mk-reco__body{flex:1 1 auto;padding:50px 72px 46px;display:flex;flex-direction:column}
.mk-reco .mk-title{font-size:72px}.mk-reco .mk-title.t-sm{font-size:56px}
.mk-chips{display:flex;flex-wrap:wrap;gap:14px;margin-top:30px}
.mk-chips span{border:2px solid var(--gold);color:var(--gold);padding:10px 22px;font-size:26px;font-weight:600;border-radius:40px}
.mk-reco__prices{display:flex;align-items:stretch;gap:24px;margin-top:34px}
.mk-reco__price{font-size:76px;font-weight:700;letter-spacing:-.02em}
.mk-reco__save{background:var(--gold);color:var(--ink);padding:14px 28px;border-radius:6px}
.mk-reco__save small{display:block;font-size:22px;font-weight:600;letter-spacing:.16em;text-transform:uppercase}
.mk-reco__save b{font-size:48px;font-weight:700}
.mk-reco__foot{margin-top:auto;display:flex;justify-content:space-between;align-items:center;padding-top:24px;font-size:30px;font-weight:600}
.mk-reco__foot img{height:92px}

/* Carrossel */
.mk-car-photos{background:var(--ink)}
.mk-grid{flex:1 1 auto;display:flex;gap:10px;min-height:0}
.mk-grid>div{display:flex;flex-direction:column;gap:10px;min-width:0;min-height:0}
.mk-grid .mk-photo{flex:1 1 0}
.mk-grid--col{flex-direction:column}.mk-grid--col>div{flex-direction:row}
.mk-car-bar{flex:0 0 150px;background:var(--ink);display:flex;align-items:center;justify-content:space-between;padding:0 56px;gap:30px}
.mk-car-bar b{font-size:38px;font-weight:700}.mk-car-bar small{display:block;font-size:24px;color:var(--gold);margin-top:6px}
.mk-car-bar img{height:92px}
.mk-car-bar .n{font-size:28px;font-weight:600;color:var(--gold)}
.mk-car-text{padding:96px 72px 72px;flex:1 1 auto;display:flex;flex-direction:column}
.mk-car-text h2{font-size:74px;font-weight:700;letter-spacing:-.02em;margin-bottom:12px}
.mk-car-text .mk-list li{font-size:40px;margin-bottom:30px}
.mk-car-equip{background:#141414}
.mk-car-specs{background:var(--cream);color:var(--ink)}
.mk-car-specs h2{color:var(--ink)}
.mk-car-specs dl{margin-top:36px}
.mk-car-specs .row{display:flex;justify-content:space-between;align-items:baseline;border-bottom:2px solid rgba(17,17,17,.14);padding:30px 0;font-size:40px}
.mk-car-specs dt{font-weight:400;color:#6b6258}.mk-car-specs dd{font-weight:700}
.mk-car-cta{background:var(--wine);align-items:center;justify-content:center;text-align:center;padding:90px 80px}
.mk-car-cta img{height:260px;margin-bottom:56px}
.mk-car-cta h2{font-size:76px;font-weight:700;letter-spacing:-.02em;line-height:1.08}
.mk-car-cta p{font-size:40px;margin-top:40px;color:var(--gold);font-weight:600;letter-spacing:.04em}
.mk-car-cta .price{font-size:56px;font-weight:700;margin-top:34px}

/* Texto: educativos e serviços */
.mk-txt{padding:72px;background:var(--ink)}
.mk-txt__head{display:flex;justify-content:space-between;align-items:center;flex:0 0 auto}
.mk-txt__head img{height:100px;width:auto}
.mk-txt__label{font-size:26px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--gold)}
.mk-txt__main{flex:1 1 auto;display:flex;flex-direction:column;min-height:0;overflow:hidden;padding:36px 0}
.mk-txt__main>:first-child{margin-top:auto}.mk-txt__main>:last-child{margin-bottom:auto}
.mk-txt__foot{flex:0 0 auto;display:flex;justify-content:space-between;align-items:center;border-top:2px solid rgba(181,145,81,.5);padding-top:30px;font-size:32px;font-weight:600}
.mk-txt__foot span:last-child{color:var(--gold)}
.mk-txt .mk-title.t-lg{font-size:84px}.mk-txt .mk-title.t-md{font-size:70px}.mk-txt .mk-title.t-sm{font-size:58px}
.mk-cover{background:linear-gradient(160deg,var(--wine),var(--wine-deep))}
.mk-cover .mk-title.t-lg{font-size:104px}.mk-cover .mk-title.t-md{font-size:88px}.mk-cover .mk-title.t-sm{font-size:72px}
.mk-dica{background:linear-gradient(160deg,var(--wine),var(--wine-deep))}
.mk-dica__item b{display:block;font-size:46px;font-weight:700;color:var(--gold);margin-bottom:18px}
.mk-dica__item p{font-size:40px;line-height:1.3;white-space:pre-line}
.mk-faq__q{font-size:68px;font-weight:700;letter-spacing:-.02em;line-height:1.08;margin-bottom:44px}
.mk-faq__a{background:var(--cream);color:var(--ink);padding:44px 48px;border-radius:10px;font-size:38px;line-height:1.32;white-space:pre-line}
.mk-steps-slide{background:var(--cream);color:var(--ink)}
.mk-steps-slide .mk-sub,.mk-erros .mk-sub{opacity:.7}
.mk-steps-slide .mk-txt__foot{border-top-color:rgba(17,17,17,.2)}
.mk-steps-slide .mk-txt__foot span:last-child,.mk-steps-slide .mk-txt__label{color:var(--gold-deep)}
.mk-steps{margin-top:40px}
.mk-step{display:flex;align-items:flex-start;gap:28px;margin-bottom:26px}
.mk-step__n{flex:0 0 auto;width:64px;height:64px;border-radius:50%;background:var(--wine);color:#fff;font-size:34px;font-weight:700;display:flex;align-items:center;justify-content:center}
.mk-step b{display:block;font-size:38px;font-weight:700;line-height:1.18}
.mk-step p{font-size:30px;line-height:1.3;margin-top:6px;opacity:.78;white-space:pre-line}
.mk-step--x .mk-step__n{background:var(--wine);color:#fff;border-radius:10px;font-size:44px}
.mk-serv__photo{flex:0 0 auto;height:380px;border-radius:14px;margin-bottom:40px}
.mk-serv__list{margin-top:36px}
.mk-serv__list li small{display:block;font-size:28px;font-weight:400;opacity:.75;margin-top:6px}
.mk-mito{background:var(--ink)}
.mk-mito__top{flex:1 1 0;background:var(--wine);padding:90px 72px 56px;display:flex;flex-direction:column;justify-content:center}
.mk-mito__bottom{flex:1 1 0;background:var(--ink);padding:56px 72px 90px;display:flex;flex-direction:column;justify-content:center;position:relative}
.mk-mito small{font-size:28px;font-weight:700;letter-spacing:.26em;text-transform:uppercase;color:var(--gold);margin-bottom:22px}
.mk-mito p{font-size:54px;font-weight:700;letter-spacing:-.01em;line-height:1.14;white-space:pre-line}
.mk-mito__bottom p{font-weight:600;font-size:44px;line-height:1.26}
.mk-mito__n{position:absolute;right:72px;bottom:44px;font-size:28px;font-weight:600;color:var(--gold)}
.mk-edu{background:var(--cream);color:var(--ink)}
.mk-edu__n{font-size:180px;font-weight:700;color:var(--gold);letter-spacing:-.04em;line-height:1;margin-bottom:24px}
.mk-edu__a{font-size:70px;font-weight:700;letter-spacing:-.02em;line-height:1.1;margin-bottom:34px}
.mk-edu__b{font-size:40px;line-height:1.34;color:#3d3830;white-space:pre-line}
.mk-edu .mk-txt__foot{border-top-color:rgba(17,17,17,.2);color:var(--gold-deep)}
.mk-erros{background:#141414}
/* Formato quadrado com muitos itens: tudo mais compacto para caber sem sobrepor */
.mk-dense{padding:56px}
.mk-dense .mk-txt__head img{height:72px}
.mk-dense .mk-txt__main{padding:24px 0}
.mk-dense .mk-title.t-lg,.mk-dense .mk-title.t-md,.mk-dense .mk-title.t-sm{font-size:50px}
.mk-dense .mk-sub{font-size:26px;margin-top:8px}
.mk-dense .mk-steps{margin-top:26px}
.mk-dense .mk-step{margin-bottom:14px;gap:22px}
.mk-dense .mk-step__n{width:52px;height:52px;font-size:28px}
.mk-dense .mk-step b{font-size:31px}
.mk-dense .mk-step p{font-size:24px;margin-top:2px}
.mk-dense .mk-txt__foot{font-size:28px;padding-top:22px}
.mk-dense .mk-serv__photo{height:240px;margin-bottom:24px}
.mk-dense .mk-serv__list{margin-top:24px}
.mk-dense .mk-serv__list li{font-size:30px;margin-bottom:14px}
.mk-dense .mk-serv__list li small{font-size:24px}

/* Testemunho */
.mk-quote{background:linear-gradient(160deg,var(--wine),var(--wine-deep))}
.mk-stars{position:relative;height:64px;margin-bottom:44px;flex:0 0 auto}
.mk-stars__row{display:flex;height:64px;position:relative}
.mk-stars__fill{position:absolute;left:0;top:0;height:64px;overflow:hidden}
.mk-stars__fill .mk-stars__row{position:relative}
.mk-quote__text{font-weight:600;letter-spacing:-.01em;line-height:1.22;white-space:pre-line}
.mk-quote__text.q-lg{font-size:78px}.mk-quote__text.q-md{font-size:56px}.mk-quote__text.q-sm{font-size:42px}
.mk-quote__by{margin-top:44px;font-size:34px}
.mk-quote__by b{display:block;font-weight:700}
.mk-quote__by span{display:block;color:var(--gold);font-weight:600;margin-top:6px;font-size:28px;letter-spacing:.1em;text-transform:uppercase}
.mk-import .mk-title.t-lg{font-size:84px}.mk-import .mk-title.t-md{font-size:70px}.mk-import .mk-title.t-sm{font-size:58px}
.mk-import__cta{margin-top:34px;font-size:30px;font-weight:600;display:flex;justify-content:space-between;align-items:center;border-top:2px solid rgba(181,145,81,.6);padding-top:24px}
.mk-import__cta span{color:var(--gold);font-weight:700}
</style>
