import axios, { AxiosProgressEvent, CancelTokenSource } from "axios";
import bootstrap from "bootstrap";

class FileUploader {
    private modalEl: HTMLElement;
    private nameInputEl: HTMLInputElement;
    private nameInputFeedbackEl: HTMLElement;
    private fileInputEl: HTMLInputElement;
    private fileInputFeedbackEl: HTMLElement;
    private alertBoxEl: HTMLElement;
    private uploadButtonEl: HTMLButtonElement;
    private cancelButtonEl: HTMLButtonElement;
    private progressWrapperEl: HTMLElement;
    private progressBarEl: HTMLElement;
    private progressTextEl: HTMLElement;

    private cancelUpload: Function;

    constructor(
        modalId: string,
        nameInputId: string,
        nameInputFeedbackId: string,
        fileInputId: string,
        fileInputFeedbackId: string,
        alertBoxId: string,
        uploadButtonId: string,
        cancelButtonId: string,
        progressWrapperId: string,
        progressBarId: string,
        progressTextId: string
    ) {
        this.modalEl = document.getElementById(modalId)!;
        this.nameInputEl = document.getElementById(
            nameInputId
        )! as HTMLInputElement;
        this.fileInputEl = document.getElementById(
            fileInputId
        )! as HTMLInputElement;
        this.nameInputFeedbackEl = document.getElementById(
            nameInputFeedbackId
        ) as HTMLElement;
        this.alertBoxEl = document.getElementById(alertBoxId)!;
        this.fileInputFeedbackEl = document.getElementById(
            fileInputFeedbackId
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
        this.progressTextEl = document.getElementById(progressTextId)!;

        this.init();
    }

    private init(): void {
        this.setupUploadButton();
        this.setupModal();
    }

    private setupUploadButton(): void {
        this.uploadButtonEl.addEventListener("click", (e) => {
            e.preventDefault();
            this.uploadFile();
        });
    }

    private setupModal(): void {
        // モーダルが消されたらアラートや入力内容をリセット
        this.modalEl.addEventListener("hidden.bs.modal", (e) => {
            this.clearInput();
            this.cancelUpload();
            // リセット
            this.cancelUpload = () => {};
            
            this.resetProgress();
            this.enabledUploadButton();
            // this.clearAlert();
        });
    }

    private clearInput(): void {
        this.nameInputEl.value = "";
        this.nameInputFeedbackEl.innerText = "";
        this.fileInputEl.value = "";
        this.fileInputFeedbackEl.innerText = "";
    }

    // private clearAlert(): void {
    //     this.alertBoxEl.classList.add("d-none");
    //     this.alertBoxEl.innerText = "";
    // }

    private uploadFile(): void {
        // アップロードボタンを無効化する
        this.disabledUploadButton();

        // CancelTokenSourceを作成
        const source = axios.CancelToken.source();

        // 入力データを取得
        const photoGroupName = this.nameInputEl.value.trim();
        const zipFile = this.fileInputEl.files![0];

        const isInputValid = this.isInputValid(photoGroupName, zipFile);
        if (!isInputValid) {
            // アップロードボタンを有効に戻す
            this.enabledUploadButton();
            return;
        }

        const formData = new FormData();
        formData.append("photoGroupName", photoGroupName);
        formData.append("zipFile", zipFile);

        console.log("アップロード開始");

        axios
            .post("/", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
                cancelToken: source.token,
                onUploadProgress: (e) => this.updateProgress(e),
            })
            .then(() => this.handleUploadSuccess())
            .catch((err) => {
                if (axios.isCancel(err)) {
                    this.handleUploadCancel();
                } else {
                    this.handleUploadError();
                }
            });

        this.cancelUpload = () => { source.cancel("アップロードが中断されました。"); }
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

    private isInputValid(photoGroupName: string, zipFile: File): boolean {
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
            console.warn("写真グループ名を入力してください。");
        }

        if (zipFile) {
            this.fileInputEl.classList.add("is-valid");
            this.fileInputEl.classList.remove("is-invalid");
            this.fileInputFeedbackEl.innerText = ""
        } else {
            isValid = false;
            this.fileInputEl.classList.add("is-invalid");
            this.fileInputEl.classList.remove("is-valid");
            this.fileInputFeedbackEl.innerText = "zipファイルを選択してください。";
            console.warn("zipファイルを選択してください。");
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
            this.progressTextEl.innerText = `${percentComplete}% アップロード完了`;
        }
    }

    private resetProgress(): void {
        this.progressWrapperEl.style.display = "none";
        this.progressWrapperEl.setAttribute("aria-valuenow", "0");
        this.progressBarEl.style.width = "0";
        this.progressBarEl.innerText = "";
        this.progressTextEl.innerText = "";
    }

    private handleUploadSuccess(): void {
        this.progressTextEl.innerText = "アップロード完了";
        this.closeModal();
    }

    private handleUploadCancel(): void {
        this.resetProgress();
        this.enabledUploadButton();
    }

    private handleUploadError(): void {
        this.progressTextEl.innerText = "アップロードに失敗しました。";
        this.resetProgress();
        this.enabledUploadButton();
        this.closeModal();
    }

    private closeModal(): void {
        console.log('closeModal');
        const modal = new bootstrap.Modal(this.modalEl);
        console.log(modal);
        modal.hide();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new FileUploader(
        "uploadModal",
        "nameInput",
        "nameInputFeedback",
        "fileInput",
        "fileInputFeedback",
        "alertBox",
        "uploadButton",
        "cancelButton",
        "progressWrapper",
        "progressBar",
        "progressText"
    );
});
