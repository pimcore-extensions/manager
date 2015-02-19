pimcore.registerNS("pimcore.plugin.manager.startup");

pimcore.plugin.manager.startup = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.manager.startup";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
    pimcoreReady: function (params,broker) {
    
    
        pimcore.globalmanager.get("layout_toolbar").extrasMenu.items.find(function(record) {
            if(record.iconCls == 'pimcore_icon_extensionmanager')
                record.setHandler(function(){
                    try {
                        pimcore.globalmanager.get("manager_admin").activate();
                    }
                    catch (e) {
                        pimcore.globalmanager.add("manager_admin", new pimcore.plugin.manager.admin());
                    }
                });
        }, this);
    }
});

var managerPlugin = new pimcore.plugin.manager.startup();

