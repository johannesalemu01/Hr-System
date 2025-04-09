import { defineStore } from "pinia";

const useSideBarStore = defineStore("sidebar", {
    state: () => ({
        isSideBarOpen: false,
    }),
    actions: {
        toggleSideBar() {
            this.isSideBarOpen = !this.isSideBarOpen;
        },
        openSideBar() {
            this.isSideBarOpen = true;
        },
        closeSideBar() {
            this.isSideBarOpen = false;
        },
    },
});

export default useSideBarStore;
