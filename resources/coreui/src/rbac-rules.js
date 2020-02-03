const rules = {
  guest: {
    static: [],
    dynamic: {}
  },
  student: {
      static: [
        "dashboard-page:visit",
        "internship:add",
        "internship:delete",
        "student-report:delete",
        "student-report:add",
        "job-description:add",
        "job-description:delete"
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
        "internship:print",
        "cluster:generate",
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
        "internship:approve",
        "internship:delete",
        "student-report:delete",
        "user-role:change",
        "internship:print",
        "cluster:generate",
      ]
    }
  };
  
  export default rules;