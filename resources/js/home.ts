import axios, { AxiosProgressEvent } from "axios";
import { Modal } from "bootstrap";
import { NoticeType, CommonNotice } from "./app";

class ModalFileUploader {
    private modalEl: HTMLElement;
    private nameInputEl: HTMLInputElement;
    private nameInputFeedbackEl: HTMLElement;
    private zipFileInputEl: HTMLInputElement;
    private zipFileInputFeedbackEl: HTMLElement;
    private gpxFileInputEl: HTMLInputElement;
    private gpxFileInputFeedbackEl: HTMLElement;
    private uploadButtonEl: HTMLButtonElement;
    private cancelButtonEl: HTMLButtonElement;
    private progressWrapperEl: HTMLElement;
    private progressBarEl: HTMLElement;

    private cancelUpload = () => {};

    constructor(
        modalId: string,
        nameInputId: string,
        nameInputFeedbackId: string,
        zipFileInputId: string,
        zipFileInputFeedbackId: string,
        gpxFileInputId: string,
        gpxFileInputFeedbackId: string,
        uploadButtonId: string,
        cancelButtonId: string,
        progressWrapperId: string,
        progressBarId: string
    ) {
        this.modalEl = document.getElementById(modalId)!;
        this.nameInputEl = document.getElementById(
            nameInputId
        )! as HTMLInputElement;
        this.nameInputFeedbackEl = document.getElementById(
            nameInputFeedbackId
        ) as HTMLElement;
        this.zipFileInputEl = document.getElementById(
            zipFileInputId
        )! as HTMLInputElement;
        this.zipFileInputFeedbackEl = document.getElementById(
            zipFileInputFeedbackId
        ) as HTMLElement;
        this.gpxFileInputEl = document.getElementById(
            gpxFileInputId
        )! as HTMLInputElement;
        this.gpxFileInputFeedbackEl = document.getElementById(
            gpxFileInputFeedbackId
        ) as HTMLElement;
        this.uploadButtonEl = document.getElementById(
            uploadButtonId
        )! as HTMLButtonElement;
        this.cancelButtonEl = document.getElementById(
            cancelButtonId
        )! as HTMLButtonElement;
        this.progressWrapperEl = document.getElementById(
            progressWrapperId
        )! as HTMLElement;
        this.progressBarEl = document.getElementById(progressBarId)!;

        this.init();
    }

    private init(): void {
        this.setupUploadButton();
        this.setupCloseModal();
    }

    private setupUploadButton(): void {
        this.uploadButtonEl.addEventListener("click", (e) => {
            e.preventDefault();
            this.uploadFile();
        });
    }

    private closeModal(): void {
        const modal = Modal.getOrCreateInstance(this.modalEl);
        modal.hide();
        this.resetModal();
    }

    private resetModal(): void {
        this.clearInput();
        this.resetProgress();
        this.enabledUploadButton();
    }

    private setupCloseModal(): void {
        this.cancelButtonEl.addEventListener("click", () => {
            this.cancelUpload();
            this.closeModal();
        });
    }

    private clearInput(): void {
        this.nameInputEl.value = "";
        this.nameInputEl.classList.remove("is-valid", "is-invalid");
        this.nameInputFeedbackEl.innerText = "";

        this.zipFileInputEl.value = "";
        this.zipFileInputEl.classList.remove("is-valid", "is-invalid");
        this.zipFileInputFeedbackEl.innerText = "";

        this.gpxFileInputEl.value = "";
        this.gpxFileInputEl.classList.remove("is-valid", "is-invalid");
        this.gpxFileInputFeedbackEl.innerText = "";
    }

    private uploadFile(): void {
        // アップロードボタンを無効化する
        this.disabledUploadButton();

        // CancelTokenSourceを作成
        const source = axios.CancelToken.source();

        // 入力データを取得
        const photoGroupName = this.nameInputEl.value.trim();
        const zipFile = this.zipFileInputEl.files![0];
        const gpxFile = this.gpxFileInputEl.files![0];

        const isInputValid = this.isInputValid(photoGroupName, zipFile, gpxFile);
        if (!isInputValid) {
            // アップロードボタンを有効に戻す
            this.enabledUploadButton();
            return;
        }

        const formData = new FormData();
        formData.append("photoGroupName", photoGroupName);
        formData.append("zipFile", zipFile);
        formData.append("gpxFile", gpxFile);

        axios
            .post("/", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
                cancelToken: source.token,
                onUploadProgress: (e) => this.updateProgress(e),
            })
            .then((res) => {
                this.handleUploadSuccess();
            })
            .catch((err) => {
                if (axios.isCancel(err)) {
                    this.handleUploadCancel();
                } else {
                    this.handleUploadError();
                }
            });

        this.cancelUpload = () => { 
            source.cancel("アップロードが中断されました。");
            this.cancelUpload = () => {};
        }
    }

    private disabledUploadButton(): void {
        this.uploadButtonEl.classList.remove("btn-primary");
        this.uploadButtonEl.classList.add("btn-secondary");
        this.uploadButtonEl.setAttribute("disabled", "");

        const uploadButtonBeforeEl = <HTMLSpanElement>(
            this.uploadButtonEl.querySelector("#uploadButtonBefore")!
        );
        const uploadButtonAfterEl = <HTMLSpanElement>(
            this.uploadButtonEl.querySelector("#uploadButtonAfter")!
        );

        uploadButtonBeforeEl.style.display = "none";
        uploadButtonAfterEl.removeAttribute("style");
    }

    private enabledUploadButton(): void {
        this.uploadButtonEl.classList.remove("btn-secondary");
        this.uploadButtonEl.classList.add("btn-primary");
        this.uploadButtonEl.removeAttribute("disabled");

        const uploadButtonBeforeEl = (
            this.uploadButtonEl.querySelector("#uploadButtonBefore")!
        ) as HTMLSpanElement;
        const uploadButtonAfterEl = (
            this.uploadButtonEl.querySelector("#uploadButtonAfter")!
        ) as HTMLSpanElement;

        uploadButtonBeforeEl.removeAttribute("style");
        uploadButtonAfterEl.style.display = "none";
    }

    private isInputValid(photoGroupName: string, zipFile: File, gpxFile: File): boolean {
        let isValid = true;

        if (photoGroupName) {
            this.nameInputEl.classList.add("is-valid");
            this.nameInputEl.classList.remove("is-invalid");
            this.nameInputFeedbackEl.innerText = "";
        } else {
            isValid = false;
            this.nameInputEl.classList.add("is-invalid");
            this.nameInputEl.classList.remove("is-valid");
            this.nameInputFeedbackEl.innerText = "写真グループ名を入力してください。";
        }

        if (zipFile) {
            this.zipFileInputEl.classList.add("is-valid");
            this.zipFileInputEl.classList.remove("is-invalid");
            this.zipFileInputFeedbackEl.innerText = ""
        } else {
            isValid = false;
            this.zipFileInputEl.classList.add("is-invalid");
            this.zipFileInputEl.classList.remove("is-valid");
            this.zipFileInputFeedbackEl.innerText = "zipファイルを選択してください。";
        }

        if (gpxFile) {
            this.gpxFileInputEl.classList.add("is-valid");
            this.gpxFileInputEl.classList.remove("is-invalid");
            this.gpxFileInputFeedbackEl.innerText = ""
        } else {
            isValid = false;
            this.gpxFileInputEl.classList.add("is-invalid");
            this.gpxFileInputEl.classList.remove("is-valid");
            this.gpxFileInputFeedbackEl.innerText = "gpxファイルを選択してください。";
        }

        return isValid;
    }

    private updateProgress(e: AxiosProgressEvent): void {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total!) * 100);
            this.progressWrapperEl.removeAttribute("style");
            this.progressWrapperEl.setAttribute(
                "aria-valuenow",
                `${percentComplete}`
            );
            this.progressBarEl.style.width = `${percentComplete}%`;
            this.progressBarEl.innerText = `${percentComplete}%`;
        }
    }

    private resetProgress(): void {
        this.progressWrapperEl.style.display = "none";
        this.progressWrapperEl.setAttribute("aria-valuenow", "0");
        this.progressBarEl.style.width = "0";
        this.progressBarEl.innerText = "";
    }

    private handleUploadSuccess(): void {
        this.closeModal();
        this.showSuccessNotification();
    }

    private handleUploadCancel(): void {
        this.showCancelNotification();
    }

    private handleUploadError(): void {
        this.closeModal();
        this.showErrorNotification();
    }

    private showSuccessNotification(): void {
        const notice = CommonNotice.get();

        const message = "アップロードが完了しました。";
        const noticeType = NoticeType.Success;
        
        notice.display(message, noticeType);
    }

    private showCancelNotification(): void {
        const notice = CommonNotice.get();

        const message = "アップロードが中止されました。";
        const noticeType = NoticeType.Warning;

        notice.display(message, noticeType);
    }

    private showErrorNotification(): void {
        const notice = CommonNotice.get();

        const message = "アップロードに失敗しました。";
        const noticeType = NoticeType.Error;

        notice.display(message, noticeType);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new ModalFileUploader(
        "uploadModal",
        "nameInput",
        "nameInputFeedback",
        "zipFileInput",
        "zipFileInputFeedback",
        "gpxFileInput",
        "gpxFileInputFeedback",
        "uploadButton",
        "cancelButton",
        "progressWrapper",
        "progressBar",
    );
});
