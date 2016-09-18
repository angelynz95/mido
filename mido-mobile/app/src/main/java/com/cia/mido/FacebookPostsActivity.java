package com.cia.mido;

import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListView;

import com.facebook.AccessToken;
import com.facebook.AccessTokenTracker;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class FacebookPostsActivity extends AppCompatActivity {
    private AccessToken accessToken;
    private AccessTokenTracker accessTokenTracker;
    private CallbackManager callbackManager;
    private SharedPreferences sharedPreferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(getApplicationContext());
        callbackManager = CallbackManager.Factory.create();

        setContentView(R.layout.activity_facebook_posts);

        getAccessToken();
        showFacebookPosts();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        accessTokenTracker.stopTracking();
    }

    @Override
    public void onBackPressed() {
        Intent facebookPagesIntent = new Intent(this, FacebookPagesActivity.class);
        startActivity(facebookPagesIntent);
        finish();
    }

    private void getAccessToken() {
        accessTokenTracker = new AccessTokenTracker() {
            @Override
            protected void onCurrentAccessTokenChanged(AccessToken oldAccessToken, AccessToken currentAccessToken) {
                accessToken = currentAccessToken;
                if (accessToken == null) {
                    Intent facebookLoginIntent = new Intent(FacebookPostsActivity.this, FacebookLoginActivity.class);
                    startActivity(facebookLoginIntent);
                    finish();
                }
            }
        };
        accessToken = AccessToken.getCurrentAccessToken();
    }

    public void chooseSocialManager(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    public void chooseNews(View view) {
        Intent newsIntent = new Intent(this, NewsActivity.class);
        startActivity(newsIntent);
        finish();
    }

    public void chooseMarketInsight(View view) {
        Intent marketIntent = new Intent(this, MarketInsightActivity.class);
        startActivity(marketIntent);
        finish();
    }

    public void chooseEmployeeSearch(View view) {
        Intent employeeSearchIntent = new Intent(this, EmployeeSearchActivity.class);
        startActivity(employeeSearchIntent);
        finish();
    }

    private void showFacebookPosts() {
        sharedPreferences = getSharedPreferences(getResources().getString(R.string.packageName), MODE_PRIVATE);
        String userId = sharedPreferences.getString("userId", null);
        String facebookPageId = sharedPreferences.getString("facebookPageId", null);
        String facebookPostsUrl = ((getResources().getString(R.string.facebookPostsUrl)).replace("{userId}", userId)).replace("{pageId}", facebookPageId);
        String url = getResources().getString(R.string.backEndUrl) + facebookPostsUrl;
        Client client = new Client(url, null, "GET");
        client.execute();

        JSONObject response;
        List<String> facebookPostContents = new ArrayList<>();
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);

            JSONArray facebookPosts = (JSONArray) response.get("posts");
            Log.d("Facebook Posts", facebookPosts.toString());
            for (int i = 0; i < facebookPosts.length(); i++) {
                JSONObject facebookPost = (JSONObject) facebookPosts.get(i);
                String facebookPostContent = facebookPost.getString("message");
                facebookPostContents.add(facebookPostContent);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        ArrayAdapter adapter = new ArrayAdapter<String>(this, R.layout.facebook_post_listview, facebookPostContents);
        ListView facebookPostList = (ListView) findViewById(R.id.facebookPostList);
        facebookPostList.setAdapter(adapter);
    }

    public void showFacebookPagePostForm(View view) {
        Intent facebookPostFormIntent = new Intent(this, FacebookPostFormActivity.class);
        startActivity(facebookPostFormIntent);
        finish();
    }
}
