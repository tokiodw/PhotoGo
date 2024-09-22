import { Toast } from "bootstrap";

export const NoticeType = {
    Success: 0,
    Warning: 1,
    Error: 2,
} as const;

export type NoticeType = typeof NoticeType[keyof typeof NoticeType];

export class CommonNotice {
    private toastEl: HTMLElement;
    private messageEl: HTMLElement;

    constructor(toastId: string, messageId: string) {
        this.toastEl = document.getElementById(toastId) as HTMLElement;
        this.messageEl = document.getElementById(messageId) as HTMLElement;        
    }

    display(message: string, noticeType: NoticeType): void {
        this.reset();
        this.messageEl.innerText = message;

        switch(noticeType) {
            case NoticeType.Success:
                this.toastEl.classList.add("text-bg-success");
                break;
            case NoticeType.Warning:
                this.toastEl.classList.add("text-bg-warning");
                break;
            case NoticeType.Error:
                this.toastEl.classList.add("text-bg-danger");
                break;
        }

        const toast = Toast.getOrCreateInstance(this.toastEl);
        toast.show();
    }

    private reset(): void {
        this.toastEl.classList.remove("text-bg-success", "text-bg-warning", "text-bg-danger");
        this.messageEl.innerText = "";
    }
    static get(): CommonNotice {
        return new CommonNotice("noticeToast", "noticeMessage");
    }
}
