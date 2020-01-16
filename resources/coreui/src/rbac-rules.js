const rules = {
  guest: {
    static: [],
    dynamic: {}
  },
  student: {
      static: [
        "dashboard-page:visit",
        "internship:add",
        "internship:delete"
      ],
      dynamic: {
        "posts:edit": ({userId, postOwnerId}) => {
          if (!userId || !postOwnerId) return false;
          return userId === postOwnerId;
        }
      }
    },
    coordinator: {
      static: [
        "dashboard-page:visit",
        "sidebar-admin:visit",
        "company:add",
        "company:edit",
        "company:delete",
        "requirement:verify",
        "user-report:validate",
        "internship:approve",
      ]
    },
    superuser: {
      static: [
        "dashboard-page:visit",
        "sidebar-admin:visit",
        "company:add",
        "company:edit",
        "company:delete",
        "user:add",
        "user:search",
        "user:delete",
        "requirement:verify",
        "user-report:validate",
        "internship:add",
        "internship:approve",
        "internship:delete"
      ]
    }
  };
  
  export default rules;