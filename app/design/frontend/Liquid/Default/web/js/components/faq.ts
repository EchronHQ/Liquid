import * as $ from 'jquery';

export default class Faq {

    public constructor() {

    }

    public init(): void {

        $('.faq-item').each((index: number, element: HTMLElement): void => {

            const faqElement: JQuery = $(element);

            new FaqItem(faqElement);

        });
    }

}

class FaqItem {

    private open: boolean = false;

    public constructor(element: JQuery) {
        const question: JQuery = $(element);

        const title: JQuery = question.find('.faq-question');
        const answer: JQuery = question.find('.faq-answer-wrapper');
        const answerText: JQuery = answer.find('.faq-answer-text');

        const textHeight: number | undefined = answerText.outerHeight();
        title.on('click', (evt) => {

            if (this.open) {
                question.removeClass('open');
                answer.css('height', '0');
                answer.css('opacity', '0');
                this.open = false;
            } else {
                question.addClass('open');
                answer.css('height', textHeight + 'px');
                answer.css('opacity', '1');
                this.open = true;
            }

        });
    }
}
