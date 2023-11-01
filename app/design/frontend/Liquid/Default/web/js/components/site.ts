export default class Site {
    public static getSiteUrl(): string {
        return (window as any).siteUrl;
    }

    public static getUrl(args: string): string {
        return this.getSiteUrl() + args;
    }
}
