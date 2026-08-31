import ProjectController from './ProjectController'
import SiteEditorController from './SiteEditorController'
import BillingController from './BillingController'
import Settings from './Settings'
import Auth from './Auth'
import Admin from './Admin'
const Controllers = {
    ProjectController: Object.assign(ProjectController, ProjectController),
SiteEditorController: Object.assign(SiteEditorController, SiteEditorController),
BillingController: Object.assign(BillingController, BillingController),
Settings: Object.assign(Settings, Settings),
Auth: Object.assign(Auth, Auth),
Admin: Object.assign(Admin, Admin),
}

export default Controllers