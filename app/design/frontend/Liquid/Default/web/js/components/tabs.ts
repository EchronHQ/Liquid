import ClickEvent = JQuery.ClickEvent;
import * as $ from 'jquery';

export default class Tabs {

    public constructor(private element: JQuery, private options: any) {


    }

    public init(): void {
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

    private showTab(tabId: string) {
        this.element.find('.tab-list .tab').removeClass('selected').removeAttr('aria-selected');
        this.element.find('.tab-list .tab[data-tab="' + tabId + '"]').addClass('selected').attr('aria-selected', 'true');

        this.element.find('.tab-content .tab-panel').hide();
        this.element.find('.tab-content .tab-panel[data-tab="' + tabId + '"]').fadeIn('fast');
    }

}
