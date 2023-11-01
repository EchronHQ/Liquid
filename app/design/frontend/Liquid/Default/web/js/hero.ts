"use strict";
import * as $ from "jquery";
import Coordinates = JQuery.Coordinates;

$(() => {
    // const hero: JQuery = $('.hero').first();
    // if (hero.length === 1) {
    //     const heroBackground: HeroBackground = new HeroBackground(hero);
    //     heroBackground.draw();
    // }

});

class HeroBackground {
    private readonly hero: JQuery;
    private centerIconPosition: Coordinates | null = null;
    private centerIcon: JQuery | null = null;

    private logos: JQuery[] = [];
    private logoPositions: Coordinates[] = [];
    private dots: JQuery | null = null;

    private resizing: boolean = false;
    private activeDots: JQuery[] = [];

    public constructor(hero: JQuery) {
        this.hero = hero;

    }

    public draw(): void {
        if (this.hero === null) {
            throw new Error('Hero not found');
        }
        this.maintainAspectRatio();
        const logos: JQuery = this.hero.find('.logo_item');

        let resizeTimeout: any | null = null;
        $(window).on('resize', () => {
            this.resizing = true;
            if (this.dots !== null) {
                this.dots.children('.dot').remove();
            }
            if (resizeTimeout !== null) {
                clearTimeout(resizeTimeout);
            }
            resizeTimeout = setTimeout(() => {
                this.resizeHandle();
                this.resizing = false;
            }, 100);
        });

        this.dots = this.hero.find('.dots');
        this.centerIcon = this.hero.find('.center-icon').first();
        if (this.centerIcon === null) {
            return;
        }
        const centerIconPosition: Coordinates | null = this.getElementCenter(this.centerIcon);
        if (centerIconPosition === null) {
            console.error('Unable to draw, center icon position is null');
            return;
        }
        this.centerIconPosition = centerIconPosition;

        logos.each(((index, element) => {
            const logo: JQuery = $(element);

            const logoPosition: Coordinates | null = this.getElementCenter(logo);
            if (logoPosition === null) {
                return;
            }
            const z: number = this.logos.push(logo);
            this.logoPositions.push(logoPosition);

            logo.on('click', (e: any) => {
                const x: Coordinates = this.logoPositions[z - 1];
                this.move(x, false);
            });

        }));

        this.centerIcon.on('click tap', (e: any) => {
            this.logoPositions.forEach((value, index) => {
                const x: Coordinates = this.logoPositions[index];
                this.move(x, true);
            })

        });

        this.startClock();
    }

    private maintainAspectRatio() {
        if (this.hero !== null) {
            this.setAspectRatio(this.hero);

        }

        this.setAspectRatio($('.canvas-wrapper '));
    }

    private setAspectRatio(element: JQuery): void {

        const width: number | undefined = element.width();
        if (width !== undefined) {
            element.css({height: width});
        }

    }

    private resizeHandle(): void {
        this.maintainAspectRatio();
        // Update positions
        if (this.centerIcon !== null) {
            const centerIconPosition: Coordinates | null = this.getElementCenter(this.centerIcon);
            if (centerIconPosition === null) {
                console.error('Unable to draw, center icon position is null');
                return;
            }
            this.centerIconPosition = centerIconPosition;

            const logoPositions: Coordinates[] = [];
            for (let logo of this.logos) {
                const logoPosition: Coordinates | null = this.getElementCenter(logo);
                if (logoPosition === null) {
                    return;
                }
                logoPositions.push(logoPosition);
            }
            this.logoPositions = logoPositions;

        }

    }

    private startClock(): void {
        setTimeout(() => {
            this.startClock();
            const randomElement: Coordinates = this.logoPositions[Math.floor(Math.random() * this.logoPositions.length)];
            const towardsLogo: boolean = Math.random() < 0.5;
            this.move(randomElement, towardsLogo)
        }, Math.floor(Math.random() * 250));
    }

    private randomBetween(min: number, max: number): number { // min and max included
        return Math.floor(Math.random() * (max - min + 1) + min)
    }

    private move(logoPosition: Coordinates, towardsLogo: boolean, bounces: number = 0): void {
        if (this.resizing) {
            // console.log('Pause moving during resize');
            return;
        }
        if (this.dots === null || this.centerIconPosition === null) {
            console.error('Unable to move, dots is null');
            return;
        }
        const deviation: number = this.randomBetween(-10, 10);
        const deviation2: number = this.randomBetween(-10, 10);
        const startX: number = (towardsLogo ? this.centerIconPosition.left : logoPosition.left) + deviation2;
        const startY: number = (towardsLogo ? this.centerIconPosition.top : logoPosition.top) + deviation2;
        const endX: number = (towardsLogo ? logoPosition.left : this.centerIconPosition.left) + deviation;
        const endY: number = (towardsLogo ? logoPosition.top : this.centerIconPosition.top) + deviation;

        const dot: JQuery = $('<div></div>');

        const size: number = this.randomBetween(2, 5);
        const opacity: number = this.randomBetween(5, 10) / 10;
        dot.addClass('dot');
        dot.css({
            left: startX,
            top: startY,
            width: size,
            height: size,
            opacity: opacity

        });

        dot.appendTo(this.dots);

        // this.activeDots.push(dot);
        setTimeout(() => {
            dot.css({
                left: endX,
                top: endY
            });
            setTimeout(() => {
                dot.remove();

                // const index = this.activeDots.indexOf(dot, 0);
                // if (index > -1) {
                //     this.activeDots.splice(index, 1);
                // }
                // console.log('Active dots', this.activeDots.length);
                if (bounces <= 2) {

                    if (towardsLogo) {
                        // Pick random logo to go from
                        const randomElement: Coordinates = this.logoPositions[Math.floor(Math.random() * this.logoPositions.length)];
                        this.move(randomElement, false, bounces + 1);
                        // TODO: cleanup old dots

                    } else {
                        // Go towards logo
                        this.move(logoPosition, true, bounces + 1);
                    }
                }
            }, 1000);
        }, 100);
    }

    private getElementCenter(element: JQuery): Coordinates | null {
        if (this.dots === null) {
            console.error('Unable to get position, dots is null');
            return null;
        }
        const offset: Coordinates | undefined = element.offset();
        if (offset === undefined) {
            console.error('Unable to get position, offset is undefined');
            return null;
        }
        const dotsOffset: Coordinates | undefined = this.dots.offset();
        if (dotsOffset === undefined) {
            console.error('Unable to get position, dots offset is undefined');
            return null;
        }
        const width: number | undefined = element.width();
        const height: number | undefined = element.height();

        if (width === undefined || height === undefined) {
            console.error('Unable to get position, width or height are undefined');
            return null;
        }

        const centerX: number = (offset.left + width / 2) - dotsOffset.left;
        const centerY: number = (offset.top + height / 2) - dotsOffset.top;

        return {left: centerX, top: centerY};

    }

}
