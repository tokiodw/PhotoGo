import { Loader } from "@googlemaps/js-api-loader";
import axios from "axios";
import { CommonNotice, NoticeType } from "./app";
import { Modal } from "bootstrap";
import { Cluster, GridAlgorithm, MarkerClusterer } from "@googlemaps/markerclusterer";

interface Photo {
    id: number;
    user_id: number;
    photo_group_id: number;
    original_name: string;
    storage_dir: string;
    thumbnail_dir: string;
    taken_at: Date;
    created_at: Date;
    updated_at: Date;
    url: string;
    thumbnail_url: string;
    location: {
        id: number;
        photo_id: number;
        gpx_id: number;
        latitude: string;
        longitude: string;
        created_at: Date;
        updated_at: Date;
    };
}

type PhotoResponse = Photo[];

class PhotoGoMap {
    private loader: Loader;
    private photoGroupId: number;

    constructor() {
        this.initMapLoader();
        this.getPhotoGroupIdFromUrl();
        this.fetchPhotos();
    }

    private initMapLoader(): void {
        this.loader = new Loader({
            apiKey: "AIzaSyA9wwQnzK7VnbhdcBy6NzGTmug-w80pcLM",
            version: "beta",
        });
    }

    private getPhotoGroupIdFromUrl(): void {
        const pathSegments = window.location.pathname.split("/");
        const id = pathSegments[pathSegments.length - 1];
        this.photoGroupId = parseInt(id);
    }

    private fetchPhotos(): void {
        axios
            .get(`/map/api/${this.photoGroupId}`)
            .then((res) => {
                const photos = res.data as PhotoResponse;
                this.initMap(photos);
                console.log(photos);
            })
            .catch((err) => {
                this.getPhotosError();
            });
    }

    private getPhotosError(): void {
        const notice = CommonNotice.get();

        const message = "画像情報の取得に失敗しました。";
        const noticeType = NoticeType.Error;

        notice.display(message, noticeType);
    }

    private initMap(photos: PhotoResponse): void {
        this.loader.load().then(async () => {
            const { Map } = (await google.maps.importLibrary(
                "maps"
            )) as google.maps.MapsLibrary;
            const { AdvancedMarkerElement } = (await google.maps.importLibrary(
                "marker"
            )) as google.maps.MarkerLibrary;
            let map = new Map(document.getElementById("map") as HTMLElement, {
                mapId: "15b4b3d4d68378e5",
                zoom: 8,
            });

            const bounds = new google.maps.LatLngBounds();
            const markers: google.maps.marker.AdvancedMarkerElement[] = [];

            photos.forEach((photo) => {
                const latitude = parseFloat(photo.location.latitude);
                const longitude = parseFloat(photo.location.longitude);

                const position = new google.maps.LatLng(latitude, longitude);

                // マーカー要素を作成
                const markerEl = document.createElement("div");
                markerEl.className = "photo-marker";

                const thumbnail = document.createElement("img");
                thumbnail.src = photo.thumbnail_url;
                thumbnail.dataset.fullSizeSrc = photo.url;
                markerEl.appendChild(thumbnail);

                const marker = new AdvancedMarkerElement({
                    position: position,
                    map: map,
                    title: photo.original_name,
                    content: markerEl,
                    zIndex: 0,
                });

                // サムネイルにクリックイベントを追加
                marker.addEventListener("click", () => {
                    const modal = new Modal(
                        document.getElementById("photoModal")
                    );
                    const modalImage = document.getElementById(
                        "modalImage"
                    ) as HTMLImageElement;
                    modalImage.src = thumbnail.dataset.fullSizeSrc as string;
                    modal.show();
                });

                marker.addEventListener("mouseover", () => {
                    marker.zIndex = 1;
                });

                marker.addEventListener("mouseout", () => {
                    marker.zIndex = 0;
                });

                markers.push(marker);
                bounds.extend(position);
            });

            const markerCluester = new MarkerClusterer({
                map: map,
                markers: markers,
                algorithm: new GridAlgorithm({ maxZoom: 20, maxDistance: 4000 }),
                renderer: {
                    render: ({
                        markers,
                        position,
                    }): google.maps.marker.AdvancedMarkerElement => {
                        const advancedMarkers =
                            markers as google.maps.marker.AdvancedMarkerElement[];
                        const clusterEl =
                            this.createCustomClusterIcon(advancedMarkers);
                        const clusterMarker = new AdvancedMarkerElement({
                            position: position,
                            map: map,
                            content: clusterEl,
                            zIndex: 0,
                        });
                        
                        clusterMarker.addEventListener("click", () => {
                            const modal = new Modal(document.getElementById("photoClusterModal"));
                            const modalImageContainer = document.querySelector(".carousel-inner") as HTMLDivElement;

                            modalImageContainer.innerHTML = advancedMarkers.map((marker, index) => {
                                return `
                                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                    <img src="${marker.querySelector('img')?.dataset.fullSizeSrc}" class="h-auto modalImage" alt="Image ${index + 1}">
                                </div>
                            `;
                            }).join('') as string;
                            modal.show();
                        });

                        clusterMarker.addEventListener("mouseover", () => {
                            clusterMarker.zIndex = 1;
                        });
        
                        clusterMarker.addEventListener("mouseout", () => {
                            clusterMarker.zIndex = 0;
                        });

                        return clusterMarker;
                    },
                },
                // モーダル表示を優先する為、標準のクリックイベントは停止
                onClusterClick: (e) => {
                    e.stop;
                },
            });

            map.fitBounds(bounds);
        });
    }

    private createCustomClusterIcon(
        markers: google.maps.marker.AdvancedMarkerElement[]
    ): HTMLDivElement {
        const clusterEl = document.createElement("div");
        clusterEl.className = "custom-cluster";

        const thumbnailsEl = document.createElement("div");
        thumbnailsEl.className = "thumbnails";
        thumbnailsEl.style.position = "absolute";
        const maxThumbnails = 3;

        markers.slice(0, maxThumbnails).forEach((marker, index) => {
            const imgSrc = marker.querySelector("img")?.src;
            const thumbnail = document.createElement("img");
            thumbnail.src = imgSrc as string;
            thumbnail.className = "cluster-thumbnail";
            thumbnail.style.position = "absolute";
            thumbnail.style.left = `${index * 7 + 10}px`;
            thumbnail.style.top = `${index * 7 + 10}px`;
            thumbnail.style.zIndex = `${maxThumbnails - index}`; // Zインデックスを調整して重ね順を決定

            const aspectRatio = thumbnail.naturalWidth / thumbnail.naturalHeight;

            if (aspectRatio > 1) {
                thumbnail.classList.add("landscape");
            } else {
                thumbnail.classList.add("portrait");
            }

            thumbnailsEl.style.left = `${-(index * 4) + 5}px`;
            thumbnailsEl.style.top = `${-(index * 7) + 10}px`;
            thumbnailsEl.appendChild(thumbnail);
        });

        clusterEl.appendChild(thumbnailsEl);

        // 枚数を表示するラベルの作成
        const countLabel = document.createElement("span");
        countLabel.className = "cluster-count-label";
        countLabel.textContent = `${markers.length}`; // マーカーの総数を表示
        countLabel.style.position = "absolute";
        countLabel.style.bottom = "10px"; // ラベルの位置を調整
        countLabel.style.right = "10px";
        countLabel.style.padding = "2px 6px";
        countLabel.style.backgroundColor = "rgba(0, 0, 0, 0.7)"; // 半透明の背景
        countLabel.style.color = "#fff";
        countLabel.style.borderRadius = "15px"; // ラベルの丸みを追加
        countLabel.style.fontSize = "15px";
        countLabel.style.zIndex = `${maxThumbnails + 1}`; // サムネイルの上に表示

        clusterEl.appendChild(countLabel);
        return clusterEl;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new PhotoGoMap();
});
