import * as $ from 'jquery';
import * as Sentry from '@sentry/browser';
import ClickEvent = JQuery.ClickEvent;
import ErrorTextStatus = JQuery.Ajax.ErrorTextStatus;
import ReCaptcha = ReCaptchaV2.ReCaptcha;
import jqXHR = JQuery.jqXHR;

declare var grecaptcha: ReCaptcha;
export default class Form {

    private readonly form: JQuery;
    private submitButton: JQuery;

    public getData: Function | null = null;

    private captchaLoaded: boolean = false;

    public constructor(elementId: string, private submitUrl: string, public readonly actionKey: string) {

        this.form = $('#' + elementId);
        this.submitButton = this.form.find(':submit');
    }

    public init(): void {

        console.log('Form init');
        this.form.find('input').on('change', () => {
            console.log('Something changed in the form');

            this.loadCaptcha();
        })

        this.submitButton.on('click', (event: ClickEvent) => {
            event.preventDefault();

            this.loadCaptcha().then(() => {
                if (this.sendData !== null) {
                    this.sendData();
                }
            })

        })
    }

    private loadCaptcha(): Promise<void> {

        const x = this;
        return new Promise((resolve, reject) => {
            if (x.captchaLoaded) {
                resolve();
            } else {
                $.getScript("https://www.google.com/recaptcha/api.js?render=6LerzpoaAAAAAMYGdaLXBtnPxc2kzHi7ypUuVAt9", (data, textStatus, jqxhr) => {
                    console.debug('Recaptcha loaded');
                    x.captchaLoaded = true;
                    resolve();
                });
            }
        })
    }

    private sendData(): void {

        if (this.form === null) {
            console.error('Unable to submit form, form is null');
            return;
        }
        this.showLoading();

        grecaptcha.ready(() => {
            grecaptcha.execute('6LerzpoaAAAAAMYGdaLXBtnPxc2kzHi7ypUuVAt9', {action: this.actionKey}).then((token: string) => {
                if (this.getData === null) {
                    return null;
                }
                const data: { hasIssues: boolean; data: any } = this.getData();
                data.data.rt = token;
                data.data.time = this.getTimeInformation();
                if (data.hasIssues) {
                    this.hideLoading();
                } else {
                    $.ajax({
                        type: "POST",
                        url: this.submitUrl,
                        data: data.data,
                        success: (data, textStatus, jqXHR) => {
                            this.hideLoading();
                            if (this.form === null) {
                                return;
                            }
                            if (data.success === true) {
                                this.showSuccess();
                            } else {
                                this.showFailure();
                            }

                        },
                        error: (jqXHR: jqXHR, textStatus: ErrorTextStatus, errorThrown: string) => {
                            this.hideLoading();
                            console.log(jqXHR);
                            this.showError('Whoops, something went wrong with your request, give us an email instead while we look into this issue.')
                            let error: Error = new Error('Unable to submit form ' + this.actionKey + ': ' + textStatus + ' ' + errorThrown);
                            Sentry.captureException(error);
                        }
                    });
                }
            });
        });

    }

    private getTimeInformation(): string {
        const now: Date = new Date();

        const month: number = now.getMonth() + 1;
        // @ts-ignore
        let timezone: string | null = /\((.*)\)/.exec(now.toString())[1];
        if (timezone === null) {
            timezone = 'unknown';
        }
        return now.getFullYear() + '-'
            + this.leftPad(month, 2, '0')
            + '-' + this.leftPad(now.getDate(), 2, '0')
            + ' ' + this.leftPad(now.getHours(), 2, '0')
            + ':' + this.leftPad(now.getMinutes(), 2, '0')
            + ' (' + timezone + ' // ' + now.getTimezoneOffset() + ')';
    }

    private leftPad(str: string | number, len: number, ch = '.'): string {
        len = len - str.toString().length + 1;
        return len > 0 ? new Array(len).join(ch) + str.toString() : str.toString();
    }

    private getValue(selectorElement: string): { value: string, field: JQuery } {
        const nameField: JQuery = this.form.find(selectorElement);
        if (nameField.length !== 1) {
            throw new Error('Unable to find input for form field "' + selectorElement + '"');
        }
        nameField.removeClass('invalid');
        // TODO: input can be textfield (message)
        //  messageField.find('textarea').val()

        const element: JQuery = nameField.find('.input');
        if (element.length !== 1) {
            nameField.addClass('invalid');
            throw new Error('Unable to find input for form field "' + selectorElement + '"');
        }

        const nameFieldValue: string | number | string[] | undefined = element.val();
        if (nameFieldValue === undefined) {
            nameField.addClass('invalid');
            throw new Error('Form field value is not defined for field "' + selectorElement + '"');
        }
        return {value: nameFieldValue.toString(), field: nameField};
    }

    public getFieldValue(selectorElement: string, validation: string = ''): { value: string, valid: boolean } {

        try {
            const fieldData: { value: string, field: JQuery } = this.getValue(selectorElement);
            if (this.isEmpty(fieldData.value)) {
                fieldData.field.addClass('invalid');
                return {value: '', valid: false};
            }
            if (validation === 'email') {
                if (!this.isEmail(fieldData.value)) {
                    return {value: '', valid: false};
                }
            }
            return {value: fieldData.value, valid: true};
        } catch (e) {
            return {value: '', valid: false};
        }

    }

    private isEmpty(value: string): boolean {
        return value.toString().trim() === '';
    }

    private isEmail(value: string): boolean {
        if (this.isEmpty(value)) {
            return false;
        }
        // TODO: add email validation
        return true;
    }

    private showSuccess(): void {
        if (this.form === null) {
            console.error('Unable show success, form is null');
            return;
        }
        $('.slide').removeClass('slide-error').addClass('slide-success');
    }

    private showFailure(): void {
        if (this.form === null) {
            console.error('Unable show failure, form is null');
            return;
        }
        $('.slide').removeClass('slide-success').addClass('slide-error');
    }

    public showError(message: string): void {
        this.form.find('.message-error').html(message).show('fast');
    }

    public showLoading(): void {
        this.submitButton.addClass('loading');
    }

    public hideLoading(): void {
        this.submitButton.removeClass('loading');
    }
}
