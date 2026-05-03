import { defineStore } from "pinia";

const useSideBarStore = defineStore("sidebar", {
    state: () => ({
        isSideBarOpen: false,
        isCollapsed: false,
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
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
        }
    },
});

export default useSideBarStore;
