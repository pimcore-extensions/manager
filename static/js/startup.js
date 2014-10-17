pimcore.registerNS("pimcore.plugin.extensionmanager.startup");

pimcore.plugin.extensionmanager.startup = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.extensionmanager.startup";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
    pimcoreReady: function (params,broker) {
        pimcore.globalmanager.get("layout_toolbar").extrasMenu.add({
            text: t("Extended Extensionmanager"),
            iconCls: "pimcore_icon_extensionmanager",
            handler: function () {

                try {
                    pimcore.globalmanager.get("plugin_extensionmanager").activate();
                }
                catch (e) {
                    pimcore.globalmanager.add("plugin_extensionmanager", new pimcore.plugin.extensionsmanager.admin());
                }
            }
        });
    }
});

var extensionmanagerPlugin = new pimcore.plugin.extensionmanager.startup();

