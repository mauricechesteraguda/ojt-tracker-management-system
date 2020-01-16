const rules = {
  guest: {
    static: [],
    dynamic: {}
  },
  student: {
      static: [
        "dashboard-page:visit"
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
      ]
    }
  };
  
  export default rules;