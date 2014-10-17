
pimcore.registerNS("pimcore.plugin.extensionmanager.admin");
pimcore.plugin.extensionmanager.admin = Class.create(pimcore.extensionmanager.admin, {

    getGrid : function(){
        pimcore.plugin.extensionmanager.admin.superclass.prototype.getGrid.call(this)
        
        this.grid.getTopToolbar().insert(3, {
            text: t("download_plugin"),
            iconCls: "pimcore_icon_plugin_add",
            handler: function () {
                try {
                    pimcore.globalmanager.get("plugin_extensionmanager_search").activate();
                }
                catch (e) {
                    pimcore.globalmanager.add("plugin_extensionmanager_search", new pimcore.plugin.extensionmanager.search());
                }
            }.bind(this)
        });
        
        return this.grid;
    }
});