"use strict";
import * as $ from "jquery";
import Form from './components/form';
import Site from './components/site';

$(() => {
    const contactForm: ContactForm = new ContactForm('contact-form');
});

class ContactForm {

    private formX: Form;

    public constructor(elementId: string) {

        this.formX = new Form(elementId, Site.getUrl('contact/submit'), 'contact');
        this.formX.getData = () => {
            return this.getFormData();
        };
        this.formX.init();
    }

    private getFormData(): { hasIssues: boolean; data: any } {
        let hasIssues: boolean = false;

        const data: any = {};
        /**
         * Name
         */
        const nameValue: { value: string, valid: boolean } = this.formX.getFieldValue('.field-name');
        if (!nameValue.valid) {
            hasIssues = true;
        }
        data.name = nameValue.value;
        /**
         * Email
         */
        const emailValue: { value: string, valid: boolean } = this.formX.getFieldValue('.field-email', 'email');
        if (!emailValue.valid) {
            hasIssues = true;
        }
        data.email = emailValue.value;
        /**
         * Phone
         */
        const phoneValue: { value: string, valid: boolean } = this.formX.getFieldValue('.field-phone');
        if (!phoneValue.valid) {
            hasIssues = true;
        }
        data.phone = phoneValue.value;
        /**
         * Company
         */
        const companyValue: { value: string, valid: boolean } = this.formX.getFieldValue('.field-company');
        if (!companyValue.valid) {
            hasIssues = true;
        }
        data.company = companyValue.value;

        /**
         * Message
         */

        const messageValue: { value: string, valid: boolean } = this.formX.getFieldValue('.field-message');
        if (!messageValue.valid) {
            hasIssues = true;
        }
        data.message = messageValue.value;

        // const messageField: JQuery = this.formX.getElem('.field-message');
        // messageField.removeClass('invalid');
        // const messageFieldValue: string | number | string[] | undefined = messageField.find('textarea').val();
        // if (this.isEmpty(messageFieldValue)) {
        //     messageField.addClass('invalid');
        //     hasIssues = true;
        // }

        return {hasIssues, data};
    }

}
