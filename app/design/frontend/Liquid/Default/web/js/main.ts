"use strict";
import * as $ from "jquery";
import * as Sentry from '@sentry/browser';
import Site from './components/site';
import Faq from './components/faq';
import Tabs from './components/tabs';
import MouseEnterEvent = JQuery.MouseEnterEvent;
import MouseLeaveEvent = JQuery.MouseLeaveEvent;
import TriggeredEvent = JQuery.TriggeredEvent;

$((): void => {

    const scroll: Scroll = new Scroll();
    const mobileMenu: MobileMenu = new MobileMenu();
    const navigation: Navigation = new Navigation();
    const customerQuotes: CustomerQuotes = new CustomerQuotes();

    $('body').removeClass('no-animations');

    const cookieBar: CookieBar = new CookieBar();

    try {
        Sentry.init({
            dsn: "https://31b275ba68434e74939da0b6b6d9c3a9@o134345.ingest.sentry.io/6485839",
            // integrations: [new (window as any).Sentry.BrowserTracing()],

            // Set tracesSampleRate to 1.0 to capture 100%
            // of transactions for performance monitoring.
            // We recommend adjusting this value in production
            tracesSampleRate: 1.0,
        });
    } catch (e) {

    }

    const faq: Faq = new Faq();
    faq.init();

    console.log('[Framework] Init components');
    $('div[data-liquid-init]').each((index: number, element: HTMLElement): void => {

        const initData: {
            control: string | undefined; options: any | undefined
        } | undefined = $(element).data('liquid-init');
        if (initData === undefined) {
            console.error('Init data undefined', {element});
            return;
        }


        console.log('[Framework] Init component', {element, initData});
        if (initData.hasOwnProperty('control') && initData.control !== undefined) {
            const control: string = initData.control;
            const options: any | null = initData.options === undefined ? null : initData.options;
            switch (control) {
                case 'tabs':

                    const tabs: Tabs = new Tabs($(element), options);
                    tabs.init();
                    break;
            }

        }


    });

});

class Scroll {
    private isScroll: boolean = false;

    public constructor() {

        $(window).on('scroll', () => {
            this.updateScroll();
        });

        this.updateScroll();
    }

    private updateScroll(): void {
        const scrollPosition: number | undefined = $(window).scrollTop();
        if (scrollPosition !== undefined && scrollPosition >= 1) {
            if (!this.isScroll) {
                $('body').addClass("scroll");
                this.isScroll = true;
            }
        } else {
            if (this.isScroll) {
                $('body').removeClass("scroll");
                this.isScroll = false;
            }
        }
    }
}

class MobileMenu {
    private isOpen = false;

    public constructor() {
        $('.menu-button').on('click', () => {
            this.toggle();
        });

        $('.site-header .mobile-navigation .wrapper .close,.site-header .mobile-navigation .overlay').on('click', () => {
            this.close();
        });
    }

    private toggle(): void {
        if (!this.isOpen) {
            this.open();
        } else {
            this.close();
        }
    }

    private open(): void {
        $('header.site-header').addClass('mobile-navigation-open');
        this.isOpen = true;
    }

    private close(): void {
        $('header.site-header').removeClass('mobile-navigation-open');
        this.isOpen = false;
    }
}

class CookieBar {
    private static COOKIE_KEY_MANAGED: string = 'managed';
    private static COOKIE_KEY_ACCEPTED: string = 'accepted';

    private bar: JQuery;
    private cookiesEnable: JQuery;
    private cookiesDisable: JQuery;

    public constructor() {

        this.bar = $('.cookies');
        this.cookiesEnable = $('.cookies-accept');
        this.cookiesDisable = $('.cookies-disable');

        $('.cookies-settings').on('click', () => {
            this.settings();
        });

        this.cookiesDisable.on('click', () => {
            this.disable();
        });
        this.cookiesEnable.on('click', () => {
            this.accept();
        });

        if (!this.isManaged()) {
            this.bar.show();
        }
        this.updateView();
    }

    private updateView(): void {
        if (this.isEnabled()) {
            this.cookiesDisable.show();
            this.cookiesEnable.hide();
        } else {
            this.cookiesDisable.hide();
            this.cookiesEnable.show();
        }
    }

    private isManaged(): boolean {
        const managed: string | null = Cookie.get(CookieBar.COOKIE_KEY_MANAGED);
        return managed === '1';
    }

    private isEnabled(): boolean {
        const accepted: string | null = Cookie.get(CookieBar.COOKIE_KEY_ACCEPTED);
        return accepted === '1';
    }

    private settings(): void {
        Cookie.set(CookieBar.COOKIE_KEY_MANAGED, '1', 2147483647);
        this.bar.hide();
        window.location.href = Site.getUrl('legal/cookies#manage');
    }

    private accept(): void {

        Cookie.set(CookieBar.COOKIE_KEY_ACCEPTED, '1', 2147483647);
        Cookie.set(CookieBar.COOKIE_KEY_MANAGED, '1', 2147483647);
        this.bar.hide();
        this.updateView();
    }

    private disable(): void {
        Cookie.set(CookieBar.COOKIE_KEY_ACCEPTED, '0', 2147483647);
        Cookie.set(CookieBar.COOKIE_KEY_MANAGED, '1', 2147483647);
        this.bar.hide();
        this.updateView();
    }
}

class Navigation {
    private subNavigation: JQuery | null = null;
    private wrapperContainer: JQuery | null = null;
    private wrapper: JQuery | null = null;
    private closeTimeout: number | null = null;

    private navigation: JQuery | null = null;

    private dropdowns: any = {};

    public constructor() {

        this.init();
    }

    private isMobile(): boolean {

        return document.body.clientWidth < 1020;
    }

    private init(): void {
        this.wrapperContainer = $('.sub-navigation .wrapper-container');
        this.wrapper = this.wrapperContainer.children('.wrapper');

        this.navigation = $('header.site-header .navigation');

        const menuItemToggles: JQuery = this.navigation.find('.nav-item[aria-haspopup="true"] .nav-item-toggle');
        menuItemToggles.on('mouseenter', (event: MouseEnterEvent) => {

            if (!this.isMobile()) {
                this.open();
                const menuItem: JQuery = $(event.target).parent();
                this.showContent(menuItem);
            }

            // const subMenuIdentifier: string = $(event.target).data('submenu');

        });

        menuItemToggles.on('click', (event: TriggeredEvent) => {
            if (this.isMobile()) {
                this.open();
                const menuItem: JQuery = $(event.target).parent();
                this.showContent(menuItem);
            }
        });
        menuItemToggles.on('mouseleave', (event: MouseLeaveEvent) => {
            if (!this.isMobile()) {
                this.close();
            }

        });

        this.wrapperContainer.on('mouseenter', (event: MouseEnterEvent) => {
            if (!this.isMobile()) {
                this.open();
            }
        });
        this.wrapperContainer.on('mouseleave', (event: MouseLeaveEvent) => {
            if (!this.isMobile()) {
                this.close();
            }
        });

        /**
         * Populate dropdown
         */
        const menuItems: JQuery = this.navigation.find('.nav-item[aria-haspopup="true"]');
        const dropdowns: JQuery = menuItems.children('.nav-item-dropdown');

        dropdowns.each(((index: number, element: HTMLElement) => {

            if (this.wrapper === null) {
                console.error('Unable to initialize menu');
                return;
            }

            const dropdown: JQuery = $(element);
            dropdown.data('x', index);

            const clone: JQuery = dropdown.clone();
            clone.data('x', index);

            this.dropdowns[index] = clone;

            this.wrapper.append(clone);
        }));

        // this.wrapper.show();
        //
        // this.open();
        // this.showContent($($('.nav-item')[1]));

    }

    private showContent(menuItem: JQuery): void {
        if (this.wrapper === null || this.wrapperContainer === null) {
            console.error('Unable to show dropdown: wrapper or wrapper container is null');
            return;
        }
        if (this.navigation === null) {
            console.error('Unable to show dropdown: navigation not found');
            return;
        }

        // this.navigation.find('.nav-item[aria-haspopup="true"] .nav-item-dropdown').hide();

        const dropdown: JQuery = menuItem.children('.nav-item-dropdown');

        if (this.isMobile()) {

            const isOpen: boolean = menuItem.hasClass('open');
            this.navigation.find('.nav-item[aria-haspopup="true"]').removeClass('open');
            if (isOpen) {
                // dropdown.hide();
                // menuItem.removeClass('open');
            } else {
                // dropdown.show();
                menuItem.addClass('open');
            }
            return;
        }
        this.navigation.find('.nav-item').removeClass('open').attr('aria-expanded', 'false');
        menuItem.addClass('open').attr('aria-expanded', 'true');

        const menuButton: JQuery = menuItem.children('.nav-item-toggle');

        if (dropdown.length === 0) {

            console.error('Dropdown not found', menuItem);
            return;
        }
        if (menuButton.length === 0) {
            console.error('Menu button not found', menuItem);
            return;
        }

        const bodyWidth: number | undefined = $('body').width();
        if (bodyWidth === undefined) {
            console.error('Unable to show content, body width is undefined');
            return;
        }

        const menuItemId: number = dropdown.data('x');

        const dropdownClone: JQuery = this.dropdowns[menuItemId];

        let width: number | undefined = dropdownClone.width();
        let height: number | undefined = dropdownClone.height();

        if (width === undefined || height === undefined) {
            console.error('Unable to show content, sub menu section width or height is undefined');
            return;
        }

        let left: number = menuItem.position().left;

        this.wrapperContainer
            .css('width', width + 'px')
            .css('height', height + 'px')
            .css('left', left + 'px');

        // Move a little closer without animations 100 120  => 110
        const delta: number = this.currentWrapperLeft > menuItemId ? 100 : -100;

        dropdownClone
            .addClass('no-transition')
            .css('opacity', 0)
            .css('left', delta + 'px');

        // Hide all dropdowns
        this.wrapper.find('.nav-item-dropdown')
            .css('opacity', 0)
            .css('z-index', 0);

        // Move the rest with animations
        if (this.timeout !== null) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }
        this.timeout = setTimeout(() => {
            if (this.wrapper !== null) {

                dropdownClone
                    .removeClass('no-transition')
                    .css('opacity', 1)
                    .css('left', 0)
                    .css('z-index', 100);

                this.currentWrapperLeft = menuItemId;
            }

        }, 10);

    }

    private currentWrapperLeft: number = 0;
    private timeout: any | null = null;

    private open(): void {
        if (this.closeTimeout !== null) {
            window.clearTimeout(this.closeTimeout);
        }
        $('header.site-header').addClass('sub-navigation-open');
    }

    private close(): void {
        if (this.closeTimeout !== null) {
            window.clearTimeout(this.closeTimeout);
        }
        this.closeTimeout = window.setTimeout(() => {
            // console.log('Menu closed')
            const menuItemToggles: JQuery = $('header.site-header .navigation .nav-item[aria-haspopup="true"]');
            menuItemToggles.removeClass('open');
            $('header.site-header').removeClass('sub-navigation-open');
        }, 200);

        // console.log('x', this.closeTimeout);

    }
}

class CustomerQuotes {

    private quotes: JQuery;
    private visibleIndex: number = 0;

    public constructor() {
        this.quotes = $('.customerquotes .quotes .quote');

        this.makeQuoteTextAllSameHeight();

        $('.customerquotes .nav .prev').on('click', () => {
            this.visibleIndex++;
            if (this.visibleIndex > this.quotes.length - 1) {
                this.visibleIndex = 0;
            }
            this.updateVisible();
        });
        $('.customerquotes .nav .next').on('click', () => {
            this.visibleIndex--;
            if (this.visibleIndex < 0) {
                this.visibleIndex = this.quotes.length - 1;
            }
            this.updateVisible();
        });

        this.updateVisible();
    }

    private makeQuoteTextAllSameHeight(): void {
        let maxTextHeight = 32;
        this.quotes.addClass('active');
        this.quotes.each((index: number, element: HTMLElement) => {
            const text: JQuery = $(element).find('.quote-text');
            const height: number | undefined = text.outerHeight(true);
            if (height !== undefined && height > maxTextHeight) {
                maxTextHeight = height;
            }
        });
        this.quotes.each((index: number, element: HTMLElement) => {
            $(element).find('.quote-text').css('height', maxTextHeight + 'px');
        });
        this.quotes.removeClass('active');
    }

    private updateVisible(): void {
        this.quotes.removeClass('active');
        $(this.quotes[this.visibleIndex]).addClass('active');
    }
}

class Cookie {
    public static set(key: string, value: string, expiry: number) {
        const expires: Date = new Date();
        expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString() + ';SameSite=Strict';
    }

    public static get(key: string) {
        const keyValue: RegExpMatchArray | null = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

    public static erase(key: string) {
        const keyValue: string | null = this.get(key);
        if (keyValue !== null) {
            Cookie.set(key, keyValue, -1);
        }
    }
}
