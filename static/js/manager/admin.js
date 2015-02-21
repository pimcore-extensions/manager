pimcore.registerNS('pimcore.plugin.manager.admin');
pimcore.plugin.manager.admin = Class.create(pimcore.extensionmanager.admin, {

    getGrid: function() {
        pimcore.plugin.manager.admin.superclass.prototype.getGrid.call(this)

        this.grid.getTopToolbar().insert(3, {
            text: t('download_extension'),
            iconCls: 'pimcore_icon_plugin_add',
            handler: function() {
                try {
                    pimcore.globalmanager.get('plugin_manager_search').activate();
                }
                catch (e) {
                    pimcore.globalmanager.add(
                        'plugin_manager_search',
                        new pimcore.plugin.manager.search());
                }
            }.bind(this)
        });

        return this.grid;
    }
});
