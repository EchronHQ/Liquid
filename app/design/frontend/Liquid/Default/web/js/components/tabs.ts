import ClickEvent = JQuery.ClickEvent;
import * as $ from 'jquery';

export default class Tabs {

    public constructor(private element: JQuery, private options: any) {
    }

    public init(): void {

        const elements = this.element.find('.tab-content .tab-panel');


        // let width: number = 0;
        // let height: number = 0;
        // let maxWidth: number | undefined = this.element.innerWidth();
        // if (maxWidth === undefined) {
        //     maxWidth = 500;
        // }
        // console.log('Max width ' + maxWidth);
        // elements.each(() => {
        //     let widthE: number | undefined = elements.width();
        //     if (widthE !== undefined && widthE > maxWidth) {
        //         widthE = maxWidth;
        //     }
        //     const heightE: number | undefined = elements.height();
        //
        //     if (widthE !== undefined && (width === 0 || widthE > width)) {
        //         width = widthE;
        //     }
        //     if (heightE !== undefined && (height === 0 || heightE > height)) {
        //         height = heightE;
        //     }
        // })
        // a const width: number | undefined = this.element.find('.tab-content').width();
        // const height: number | undefined = this.element.find('.tab-content').height();


        // if (width === 0 || height === 0) {
        //     throw new Error('Unable to load tabs: dimension not defined');
        // }
        //  this.element.find('.tab-content').css('width', Math.round(width)).css('height', Math.round(height));

        this.element.find('.tab-content .tab-panel').hide();
        this.element.find('.tab-list .tab').on('click', (evt: ClickEvent): void => {
            evt.stopPropagation();
            evt.preventDefault()
            const button: JQuery = $(evt.target);
            const tabKey: string = button.data('tab');
            this.showTab(tabKey);
        });
        this.showTab('0')
    }

    private showTab(tabId: string): void {
        // TODO: add some animation
        this.element.find('.tab-list .tab').removeClass('selected').removeAttr('aria-selected');
        this.element.find('.tab-list .tab[data-tab="' + tabId + '"]').addClass('selected').attr('aria-selected', 'true');

        this.element.find('.tab-content .tab-panel').fadeOut('fast');
        this.element.find('.tab-content .tab-panel[data-tab="' + tabId + '"]').fadeIn('fast');
    }


}
